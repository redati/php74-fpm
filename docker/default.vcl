# VCL version 5.0 is not supported so it should be 4.0 even though actually used Varnish version is 6
vcl 4.1;

import std;
import saintmode;
import directors;
# The minimal Varnish version is 6.0
# For SSL offloading, pass the following header in your proxy server or load balancer: 'X-Forwarded-Proto: https'

backend servidor {

    .host = "servidor";
    .port = "80";
    .first_byte_timeout = 600s;
    .connect_timeout = 600s;
    .between_bytes_timeout = 600s;

     #limite de resposta do servidor 2s
     #verificar a cada 4s
     #se 3 de 5 requisição for ok, servidor saudável e expira a cada 1 segundo
     #.probe = {
     # .url = "/health_check.php";
     # .timeout = 1s; #tempo de expiração
     # .interval = 5s; #intervalo de solciitação
     # .window = 4; #numero de socliitação
     # .threshold = 2; #quantidade de erros
     #}
}
#backend servidor2 {
#    .host = "servidor2";
#    .port = "82";
#    .first_byte_timeout = 75s;
#    .connect_timeout = 75s;
#    .between_bytes_timeout = 75s;
#    .probe = {
#      .url = "/health_check.php";
#      .timeout = 1s; #tempo de expiração
#      .interval = 8s; #intervalo de solciitação
#      .window = 4; #numero de socliitação
#      .threshold = 2; #quantidade de erros
#     }
#}

acl purge {
    "servidor";
#    "servidor2";
    "localhost";
}

#https://devdocs.magento.com/guides/v2.4/config-guide/varnish/config-varnish-advanced.html
#magento advanced init
sub vcl_init {
    # Instantiate sm1, sm2 for backends tile1, tile2
    # with 10 blacklisted objects as the threshold for marking the
    # whole backend sick.
    new sm1 = saintmode.saintmode(servidor, 10);
#    new sm2 = saintmode.saintmode(servidor2, 10);

    # Add both to a director. Use sm0, sm1 in place of tile1, tile2.
    # Other director types can be used in place of random.
    new magedirector = directors.random();
    magedirector.add_backend(sm1.backend(), 1);
 #   magedirector.add_backend(sm2.backend(), 1);
}
#end

#https://devdocs.magento.com/guides/v2.4/config-guide/varnish/config-varnish-advanced.html
#magento varnish advanced init
sub vcl_backend_fetch {
    # Get a backend from the director.
    # When returning a backend, the director will only return backends
    # saintmode says are healthy.
    set bereq.backend = magedirector.backend();
}
#end

sub vcl_recv {

    if (req.restarts > 0) {
        set req.hash_always_miss = true;
    }
    
    if (req.http.cf-connecting-ip) {
            set req.http.X-Forwarded-For = req.http.cf-connecting-ip;
    } else {
            set req.http.X-Forwarded-For = client.ip;
    }


  # Remove the proxy header (see https://httpoxy.org/#mitigate-varnish)
  unset req.http.proxy;

  # Some generic URL manipulation, useful for all templates that follow
  # First remove URL parameters used to track effectiveness of online marketing campaigns
  if (req.url ~ "(\?|&)(utm_[a-z]+|gclid|cx|ie|cof|siteurl|msclkid|fbclid)=") {
      set req.url = regsuball(req.url, "(utm_[a-z]+|gclid|cx|ie|cof|msclkid|siteurl|fbclid)=[-_A-z0-9+()%.]+&?", "");
      set req.url = regsub(req.url, "[?|&]+$", "");
  }

  # Strip hash, server doesn't need it.
  if (req.url ~ "\#") {
    set req.url = regsub(req.url, "\#.*$", "");
   }



    if (req.method == "PURGE") {
        if (client.ip !~ purge) {
            return (synth(405, "Method not allowed"));
        }
        # To use the X-Pool header for purging varnish during automated deployments, make sure the X-Pool header
        # has been added to the response in your backend server config. This is used, for example, by the
        # capistrano-magento2 gem for purging old content from varnish during it's deploy routine.
        if (!req.http.X-Magento-Tags-Pattern && !req.http.X-Pool) {
            return (synth(400, "X-Magento-Tags-Pattern or X-Pool header required"));
        }
        if (req.http.X-Magento-Tags-Pattern) {
          ban("obj.http.X-Magento-Tags ~ " + req.http.X-Magento-Tags-Pattern);
        }
        if (req.http.X-Pool) {
          ban("obj.http.X-Pool ~ " + req.http.X-Pool);
        }
        return (synth(200, "Purged"));
    }

    if (req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE") {
          /* Non-RFC2616 or CONNECT which is weird. */
          return (pipe);
    }

    # We only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Bypass shopping cart, checkout and search requests
    if (req.url ~ "/checkout" || req.url ~ "/ppadm") {
        return (pass);
    }
    if (req.url ~ "/catalogsearch") {
        return (pass);
    }

    # Bypass health check requests
    if (req.url ~ "/health_check.php") {
        return (pass);
    }

    # Set initial grace period usage status
    set req.http.grace = "none";

    # normalize url in case of leading HTTP scheme and domain
    set req.url = regsub(req.url, "^http[s]?://", "");

    # collect all cookies
    std.collect(req.http.Cookie);

    # Compression filter. See https://www.varnish-cache.org/trac/wiki/FAQ/Compression
    if (req.http.Accept-Encoding) {
        if (req.url ~ "\.(jpg|jpeg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|flv)$") {
            # No point in compressing these
            unset req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
	} elsif (req.http.Accept-Encoding ~ "deflate" && req.http.user-agent !~ "MSIE") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            # unknown algorithm
            unset req.http.Accept-Encoding;
        }
    }

    # Remove all marketing get parameters to minimize the cache objects
    if (req.url ~ "(\?|&)(gclid|cx|ie|cof|siteurl|zanpid|origin|fbclid|mc_[a-z]+|utm_[a-z]+|_bta_[a-z]+)=") {
        set req.url = regsuball(req.url, "(gclid|cx|ie|cof|siteurl|zanpid|origin|fbclid|mc_[a-z]+|utm_[a-z]+|_bta_[a-z]+)=[-_A-z0-9+()%.]+&?", "");
        set req.url = regsub(req.url, "[?|&]+$", "");
    }

    # Static files caching
    if (req.url ~ "/pub/media/") {
        # Static files should not be cached by default
        return (pass);

        # But if you use a few locales and don't use CDN you can enable caching static files by commenting previous line (#return (pass);) and uncommenting next 3 lines
        #unset req.http.Https;
        #unset req.http.X-Forwarded-Proto;
        unset req.http.Cookie;
    }
    #cache staticos mas nao imagens
    if (req.url ~ "/pub/static/") {
        # Static files should not be cached by default
        #return (pass);
        # But if you use a few locales and don't use CDN you can enable caching static files by commenting previous line (#return (pass);) and uncommenting next 3 lines
        unset req.http.Https;
        unset req.http.X-Forwarded-Proto;
        unset req.http.Cookie;
    }



    # Authenticated GraphQL requests should not be cached by default
    if (req.url ~ "/graphql" && req.http.Authorization ~ "^Bearer") {
        return (pass);
    }

    return (hash);
}

sub vcl_hash {
    if (req.http.cookie ~ "X-Magento-Vary=") {
        hash_data(regsub(req.http.cookie, "^.*?X-Magento-Vary=([^;]+);*.*$", "\1"));
    }

    # For multi site configurations to not cache each other's content
    if (req.http.host) {
        hash_data(req.http.host);
    } else {
        hash_data(server.ip);
    }



    # To make sure http users don't see ssl warning
    if (req.http.X-Forwarded-Proto) {
        hash_data(req.http.X-Forwarded-Proto);
    }
    
   # if (req.http.user-agent ~ "(?i)iPhone\|iPod\|BlackBerry\|BB10\|Pre\|Palm\|Googlebot\-Mobile\|mobi\|Safari Mobile\|Windows Mobile\|Android\|Opera Mini\|mobile") {
     #   hash_data("5");
    #}



    if (req.url ~ "/graphql") {
        call process_graphql_headers;
    }
}

sub process_graphql_headers {
    if (req.http.Store) {
        hash_data(req.http.Store);
    }
    if (req.http.Content-Currency) {
        hash_data(req.http.Content-Currency);
    }
}

sub vcl_backend_response {

    set beresp.grace = 3d;

    if (beresp.http.content-type ~ "text") {
        set beresp.do_esi = true;
    }

    if (bereq.url ~ "\.js$" || beresp.http.content-type ~ "text") {
        set beresp.do_gzip = true;
    }

    if (beresp.http.X-Magento-Debug) {
        set beresp.http.X-Magento-Cache-Control = beresp.http.Cache-Control;
    }


    #removido duplicado
    # cache only successfully responses and 404s
    #if (beresp.status != 200 && beresp.status != 404) {
    #    set beresp.ttl = 0s;
    #    set beresp.uncacheable = true;
        #return (deliver); - > padrão magento
	#return (abandon);

    #} elsif (beresp.http.Cache-Control ~ "private") {
    #    set beresp.uncacheable = true;
    #    set beresp.ttl = 86400s;
    #    return (deliver);
    #}

    # validate if we need to cache it and prevent from setting cookie
    if (beresp.ttl > 0s && (bereq.method == "GET" || bereq.method == "HEAD")) {
        unset beresp.http.set-cookie;
    }

   # If page is not cacheable then bypass varnish for 2 minutes as Hit-For-Pass
   if (beresp.ttl <= 0s ||
       beresp.http.Surrogate-control ~ "no-store" ||
       (!beresp.http.Surrogate-Control &&
       beresp.http.Cache-Control ~ "no-cache|no-store") ||
       beresp.http.Vary == "*") {
        # Mark as Hit-For-Pass for the next 2 minutes
        set beresp.ttl = 120s;
        set beresp.uncacheable = true;
    }




     # cache only successfully responses and 404s
   if (beresp.status != 200 && beresp.status != 404) {
       set beresp.ttl = 0s;
       set beresp.uncacheable = true;
       return (deliver);
   } elsif (beresp.status > 500) { 

        #https://devdocs.magento.com/guides/v2.4/config-guide/varnish/config-varnish-advanced.html
        #magento advanced init
        # This marks the backend as sick for this specific
        # object for the next 20s.
        saintmode.blacklist(20s);
        # Retry the request. This will result in a different backend
        # being used.
        #end

       return (retry); 

   } elsif (beresp.http.Cache-Control ~ "private") {
       set beresp.uncacheable = true;
       set beresp.ttl = 86400s;
       return (deliver);
   }


  # Large static files are delivered directly to the end-user without
  # waiting for Varnish to fully read the file first.
  # Varnish 4 fully supports Streaming, so use streaming here to avoid locking.
  if (bereq.url ~ "^[^?]*\.(7z|webp|avi|bz2|flac|flv|gz|mka|mkv|mov|mp3|mp4|mpeg|mpg|ogg|ogm|opus|rar|tar|tgz|tbz|txz|wav|webm|xz|zip)(\?.*)?$") {
    unset beresp.http.set-cookie;
    set beresp.do_stream = true;  
    # Check memory usage it'll grow in fetch_chunksize blocks (128k by default) if the backend doesn't send a Content-Length header, so only enable it for big objects
  }



    return (deliver);
}

sub vcl_deliver {
    #if (resp.http.X-Magento-Debug) {
        if (resp.http.x-varnish ~ " ") {
            set resp.http.X-Magento-Cache-Debug = "HIT";
            set resp.http.Grace = req.http.grace;
        } else {
            set resp.http.X-Magento-Cache-Debug = "MISS";
        }
    #} else {
    #    unset resp.http.Age;
    #}

    # Not letting browser to cache non-static files.
    if (resp.http.Cache-Control !~ "private" && req.url !~ "^/(pub/)?(media|static)/") {
        set resp.http.Pragma = "no-cache";
        set resp.http.Expires = "-1";
        set resp.http.Cache-Control = "no-store, no-cache, must-revalidate, max-age=0";
    }

    unset resp.http.X-Magento-Debug;
    unset resp.http.X-Magento-Tags;
    unset resp.http.X-Powered-By;
    unset resp.http.Server;
    unset resp.http.X-Varnish;
    unset resp.http.Via;
    unset resp.http.Link;
}

sub vcl_hit {
    if (obj.ttl >= 0s) {
        # Hit within TTL period
        return (deliver);
    }
    if (std.healthy(req.backend_hint)) {
        if (obj.ttl + 300s > 0s) {
            # Hit after TTL expiration, but within grace period
            set req.http.grace = "normal (healthy server)";
            return (deliver);
        } else {
	    #padrao magento 2
            # Hit after TTL and grace expiration
            return (restart);

	    #alterado para https://info.varnish-software.com/blog/grace-varnish-4-stale-while-revalidate-semantics-varnish
	    # No candidate for grace. Fetch a fresh object.
            #return(fetch);
        }
    } else {
	#padrao magento 2
        # server is not healthy, retrieve from cache
        #set req.http.grace = "unlimited (unhealthy server)";
        #return (deliver);

	#alterado para //info.varnish-software.com/blog/grace-varnish-4-stale-while-revalidate-semantics-varnish
	# backend is sick - use full grace
	if (obj.ttl + obj.grace > 0s) {
            set req.http.grace = "full";
            return (deliver);
        } else {
            # no graced object.
            return (restart);
        }


    }
}
