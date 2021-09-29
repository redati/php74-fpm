<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait MapeamentoAtributos {
    // atributo
    // id do atributo
    // retorno esperado (valor, nome, configuração)
    public function AttrInfo($attr, $id, $resultado = ''){

        $attrinfo['materiais'] = $this->Materiais();
        $attrinfo['acabamentos'] = $this->Acabamentos();
        $attrinfo['tamanhos'] = $this->Tamanhos();
        $attrinfo['categorias'] = $this->Categorias();

        if (!empty($resultado)){
                return $attrinfo[$attr][$id][$resultado];
        }else {
                return $attrinfo[$attr][$id];
        }



    }
    private function Materiais() :Array {
        $materiais = [
            1893 => [ 'nome' => 'tela_canvas' ],
            1894 => [ 'nome' => 'quadrinho_adesivo' ]
        ];
        return $materiais;
    }
    // thumb_principal é imagem com tarja para o produto configurável
    private function Acabamentos () :Array {
        $acabamentos = [
            194 => [ 'nome' => 'chassi_interno',  'modelo' => 'chassi',  'thumb_principal' => true ],
            197 => [ 'nome' => 'moldura_natural', 'modelo' => 'moldura', 'thumb_principal' => false ],
            195 => [ 'nome' => 'moldura_branca',  'modelo' => 'moldura', 'thumb_principal' => false ],
            196 => [ 'nome' => 'moldura_preta',   'modelo' => 'moldura', 'thumb_principal' => true  ],
            198 => [ 'nome' => 'canvas_rolo',     'modelo' => 'rolo',    'thumb_principal' => false ]
        ];
        return $acabamentos;
    }
    private function Tamanhos () :Array {
        $tamanhos = [
            //QUADROS UNICOS
            315 =>  [ 'nome' => '30x20',  'composicao' => false, 'quadros' => 1, 'medida1' => 30,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem('proporcao_a'),  'tamanho' => $this->NomeTamanhno(30,20)  ],
            136 =>  [ 'nome' => '30x30',  'composicao' => false, 'quadros' => 1, 'medida1' => 30,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(30,30)  ],
            351 =>  [ 'nome' => '40x14',  'composicao' => false, 'quadros' => 1, 'medida1' => 40,  'medida2' => 14, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(40,14)  ],
            204 =>  [ 'nome' => '45x30',  'composicao' => false, 'quadros' => 1, 'medida1' => 45,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('proporcao_a'),  'tamanho' => $this->NomeTamanhno(45,30)  ],
            1537 => [ 'nome' => '40x40',  'composicao' => false, 'quadros' => 1, 'medida1' => 40,  'medida2' => 40, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(40,40)  ],
            348 =>  [ 'nome' => '40x15',  'composicao' => false, 'quadros' => 1, 'medida1' => 40,  'medida2' => 15, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(40,15)  ],
            338 =>  [ 'nome' => '45x45',  'composicao' => false, 'quadros' => 1, 'medida1' => 45,  'medida2' => 45, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(45,45)  ],
            167 =>  [ 'nome' => '50x18',  'composicao' => false, 'quadros' => 1, 'medida1' => 50,  'medida2' => 18, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(50,15)  ],
            134 =>  [ 'nome' => '50x35',  'composicao' => false, 'quadros' => 1, 'medida1' => 50,  'medida2' => 35, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(50,35)  ],
            295 =>  [ 'nome' => '50x50',  'composicao' => false, 'quadros' => 1, 'medida1' => 50,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(50,50)  ],
            337 =>  [ 'nome' => '55x55',  'composicao' => false, 'quadros' => 1, 'medida1' => 55,  'medida2' => 55, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(55,55)  ],
            347 =>  [ 'nome' => '60x20',  'composicao' => false, 'quadros' => 1, 'medida1' => 60,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(60,20)  ],
            168 =>  [ 'nome' => '60x21',  'composicao' => false, 'quadros' => 1, 'medida1' => 60,  'medida2' => 21, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(60,21)  ],
            342 =>  [ 'nome' => '60x45',  'composicao' => false, 'quadros' => 1, 'medida1' => 60,  'medida2' => 45, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(60,45)  ],
            170 =>  [ 'nome' => '60x60',  'composicao' => false, 'quadros' => 1, 'medida1' => 60,  'medida2' => 60, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(60,60)  ],
            339 =>  [ 'nome' => '65x33',  'composicao' => false, 'quadros' => 1, 'medida1' => 65,  'medida2' => 33, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(65,33)  ],
            200 =>  [ 'nome' => '65x35',  'composicao' => false, 'quadros' => 1, 'medida1' => 65,  'medida2' => 35, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(65,35)  ],
            210 =>  [ 'nome' => '63x42',  'composicao' => false, 'quadros' => 1, 'medida1' => 63,  'medida2' => 42, 'base' => $this->TamanhoBaseImagem('proporcao_a'),  'tamanho' => $this->NomeTamanhno(63,42)  ],
            306 =>  [ 'nome' => '65x50',  'composicao' => false, 'quadros' => 1, 'medida1' => 65,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(65,50)  ],
            305 =>  [ 'nome' => '65x56',  'composicao' => false, 'quadros' => 1, 'medida1' => 65,  'medida2' => 56, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(65,56)  ],
            336 =>  [ 'nome' => '65x65',  'composicao' => false, 'quadros' => 1, 'medida1' => 65,  'medida2' => 65, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(65,65)  ],
            349 =>  [ 'nome' => '80x28',  'composicao' => false, 'quadros' => 1, 'medida1' => 80,  'medida2' => 28, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(80,28)  ],
            340 =>  [ 'nome' => '80x40',  'composicao' => false, 'quadros' => 1, 'medida1' => 80,  'medida2' => 40, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(80,40)  ],
            341 =>  [ 'nome' => '80x58',  'composicao' => false, 'quadros' => 1, 'medida1' => 80,  'medida2' => 58, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(80,58)  ],
            294 =>  [ 'nome' => '80x80',  'composicao' => false, 'quadros' => 1, 'medida1' => 80,  'medida2' => 80, 'base' => $this->TamanhoBaseImagem('quadrado'),     'tamanho' => $this->NomeTamanhno(80,80)  ],
            214 =>  [ 'nome' => '95x53',  'composicao' => false, 'quadros' => 1, 'medida1' => 95,  'medida2' => 53, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(95,53)  ],
            138 =>  [ 'nome' => '95x63',  'composicao' => false, 'quadros' => 1, 'medida1' => 95,  'medida2' => 63, 'base' => $this->TamanhoBaseImagem('proporcao_a'),  'tamanho' => $this->NomeTamanhno(95,63)  ],
            169 =>  [ 'nome' => '100x21', 'composicao' => false, 'quadros' => 1, 'medida1' => 100, 'medida2' => 21, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(100,21) ],
            328 =>  [ 'nome' => '100x30', 'composicao' => false, 'quadros' => 1, 'medida1' => 100, 'medida2' => 30, 'base' => $this->TamanhoBaseImagem('proporcao_b'),  'tamanho' => $this->NomeTamanhno(100,30) ],
            326 =>  [ 'nome' => '100x35', 'composicao' => false, 'quadros' => 1, 'medida1' => 100, 'medida2' => 35, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(100,35) ],
            345 =>  [ 'nome' => '100x65', 'composicao' => false, 'quadros' => 1, 'medida1' => 100, 'medida2' => 65, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(100,65) ],
            327 =>  [ 'nome' => '120x35', 'composicao' => false, 'quadros' => 1, 'medida1' => 120, 'medida2' => 35, 'base' => $this->TamanhoBaseImagem('proporcao_b'),  'tamanho' => $this->NomeTamanhno(120,35) ],
            314 =>  [ 'nome' => '120x80', 'composicao' => false, 'quadros' => 1, 'medida1' => 120, 'medida2' => 80, 'base' => $this->TamanhoBaseImagem('proporcao_a'),  'tamanho' => $this->NomeTamanhno(120,80) ],
            199 =>  [ 'nome' => '120x85', 'composicao' => false, 'quadros' => 1, 'medida1' => 120, 'medida2' => 85, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(120,85) ],
            1935 => [ 'nome' => '50x90',  'composicao' => false, 'quadros' => 1, 'medida1' => 50,  'medida2' => 90, 'base' => $this->TamanhoBaseImagem(''),             'tamanho' => $this->NomeTamanhno(50,90)  ],

            //KIT 2 QUADROS
            1901 => [ 'nome' => '30x20',  'composicao' => true, 'quadros' => 2, 'medida1' => 30,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(30,20)  ],
            1921 => [ 'nome' => '30x30',  'composicao' => true, 'quadros' => 2, 'medida1' => 30,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(30,30)  ],
            1902 => [ 'nome' => '45x30',  'composicao' => true, 'quadros' => 2, 'medida1' => 45,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(45,30)  ],
            1903 => [ 'nome' => '63x42',  'composicao' => true, 'quadros' => 2, 'medida1' => 63,  'medida2' => 42, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(63,42)  ],
            1904 => [ 'nome' => '95x63',  'composicao' => true, 'quadros' => 2, 'medida1' => 95,  'medida2' => 63, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(95,63)  ],
            1909 => [ 'nome' => '40x40',  'composicao' => true, 'quadros' => 2, 'medida1' => 40,  'medida2' => 40, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(40,40)  ],
            1922 => [ 'nome' => '50x50',  'composicao' => true, 'quadros' => 2, 'medida1' => 50,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(50,50)  ],
            1910 => [ 'nome' => '60x60',  'composicao' => true, 'quadros' => 2, 'medida1' => 60,  'medida2' => 60, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(60,60)  ],
            1911 => [ 'nome' => '80x80',  'composicao' => true, 'quadros' => 2, 'medida1' => 80,  'medida2' => 80, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(80,80)  ],
            1928 => [ 'nome' => '120x80', 'composicao' => true, 'quadros' => 2, 'medida1' => 120, 'medida2' => 80, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(120,80) ],
            1931 => [ 'nome' => '60x20',  'composicao' => true, 'quadros' => 2, 'medida1' => 60,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem(''),              'tamanho' => $this->NomeTamanhno(60,20)  ],
            1932 => [ 'nome' => '80x25',  'composicao' => true, 'quadros' => 2, 'medida1' => 80,  'medida2' => 25, 'base' => $this->TamanhoBaseImagem(''),              'tamanho' => $this->NomeTamanhno(80,25)  ],
            1933 => [ 'nome' => '100x30', 'composicao' => true, 'quadros' => 2, 'medida1' => 100, 'medida2' => 30, 'base' => $this->TamanhoBaseImagem('proporcao_b'),   'tamanho' => $this->NomeTamanhno(100,30) ],
            1934 => [ 'nome' => '120x35', 'composicao' => true, 'quadros' => 2, 'medida1' => 120, 'medida2' => 35, 'base' => $this->TamanhoBaseImagem('proporcao_b'),   'tamanho' => $this->NomeTamanhno(120,35) ],

            //KIT 3 QUADROS
            1905 => [ 'nome' => '30x20',  'composicao' => true, 'quadros' => 3, 'medida1' => 30,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(30,20)  ],
            1923 => [ 'nome' => '20x20',  'composicao' => true, 'quadros' => 3, 'medida1' => 20,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(20,20)  ],
            1918 => [ 'nome' => '30x30',  'composicao' => true, 'quadros' => 3, 'medida1' => 30,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(30,30)  ],
            1906 => [ 'nome' => '45x30',  'composicao' => true, 'quadros' => 3, 'medida1' => 45,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(45,30)  ],
            1907 => [ 'nome' => '63x42',  'composicao' => true, 'quadros' => 3, 'medida1' => 63,  'medida2' => 42, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(63,42)  ],
            1908 => [ 'nome' => '95x63',  'composicao' => true, 'quadros' => 3, 'medida1' => 95,  'medida2' => 63, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(95,63)  ],
            1912 => [ 'nome' => '40x40',  'composicao' => true, 'quadros' => 3, 'medida1' => 40,  'medida2' => 40, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(40,40)  ],
            1916 => [ 'nome' => '50x50',  'composicao' => true, 'quadros' => 3, 'medida1' => 50,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(50,50)  ],
            1913 => [ 'nome' => '60x60',  'composicao' => true, 'quadros' => 3, 'medida1' => 60,  'medida2' => 60, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(60,60)  ],
            1914 => [ 'nome' => '80x80',  'composicao' => true, 'quadros' => 3, 'medida1' => 80,  'medida2' => 80, 'base' => $this->TamanhoBaseImagem('quadrado'),      'tamanho' => $this->NomeTamanhno(80,80)  ],
            1929 => [ 'nome' => '120x80', 'composicao' => true, 'quadros' => 3, 'medida1' => 120, 'medida2' => 80, 'base' => $this->TamanhoBaseImagem('proporcao_a'),   'tamanho' => $this->NomeTamanhno(120,80) ],
            1929 => [ 'nome' => '90x50',  'composicao' => true, 'quadros' => 3, 'medida1' => 90,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem(''),              'tamanho' => $this->NomeTamanhno(90,50)  ],

            //KIT 4 QUADROS
            1924 => [ 'nome' => '20x20',  'composicao' => true, 'quadros' => 4, 'medida1' => 20,  'medida2' => 20, 'base' => $this->TamanhoBaseImagem(''),          'tamanho' => $this->NomeTamanhno(20,20)  ],
            1925 => [ 'nome' => '30x30',  'composicao' => true, 'quadros' => 4, 'medida1' => 30,  'medida2' => 30, 'base' => $this->TamanhoBaseImagem(''),          'tamanho' => $this->NomeTamanhno(30,30)  ],
            1926 => [ 'nome' => '40x40',  'composicao' => true, 'quadros' => 4, 'medida1' => 40,  'medida2' => 40, 'base' => $this->TamanhoBaseImagem(''),          'tamanho' => $this->NomeTamanhno(40,40)  ],
            1927 => [ 'nome' => '50x50',  'composicao' => true, 'quadros' => 4, 'medida1' => 50,  'medida2' => 50, 'base' => $this->TamanhoBaseImagem('quadrado'),  'tamanho' => $this->NomeTamanhno(50,50)  ],
        ];
        return $tamanhos;
    }
    // return array or false
    public function Categorias ($id = 0) {
        //Mapeamento de categorias que possuem ambientes específicos
        $cat =  [
           219 => [ 'ambiente' => 'infantil'],
           235 => [ 'ambiente' => 'cozinha'],
           0 =>   [ 'ambiente' => 'generico'],
        ];
        if ($id != 0){
            if (isset($cat[$id])){
                return $cat[$id];
            } else {
                return false;
            }
        }
        return $cat[0];
    }

    // retorna largura e altura para imagem base da montagem para o site de arcordo com a proporção
    public function TamanhoBaseImagem($formato) :Array {

        // maior x menor
        $tm = [
            'quadrado'    => [ 'x' => 800, 'y' => 800, 'proporcao' =>'quadrado' ],
            // 20x30, 45x30, 63x42, 95x63, 120x80
            'proporcao_a' => [ 'x' => 874, 'y' => 589, 'proporcao' => 'proporcao_a' ],
            // 100x30 120x35
            'proporcao_b' => [ 'x' => 870, 'y' => 269, 'proporcao' => 'proporcao_b'],
        ];
        if (isset($tm[$formato])){
            return $tm[$formato];
        }else {
            //Log::debug("TamanhoBaseImagem recebeu um formato não definido");
            return [];
        }

    }

    // retorna tamannos no formato pequeno, medio ou grande
    public function NomeTamanhno($x, $y) :String {
        $nome = 'pequeno';
        if ($x > 45 or $y > 45){
            $nome = 'medio';
        }
        if ($x > 70 or $y > 70){
            $nome = 'grande';
        }
        return $nome;
    }


}


?>
