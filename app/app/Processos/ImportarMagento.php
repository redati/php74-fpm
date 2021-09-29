<?php

namespace App\Processos;

use App\Magento\Magento;
use App\Magento\RestApi;
use App\Produtos;

class ImportarMagento {

    private $data;
    private $magento;

    public function __construct()
    {
        $this->data = [];
        $this->magento = New Magento(new RestApi);
        $this->data['fields'] = 'items[id,sku,name,type_id,visibility,created_at,updated_at,extension_attributes,custom_attributes,configurable_product_links,category_ids,options,composicao]';
    }
    public function importar($de = null, $para = null, $id = null){
        if ($de != null and $para != null){
            $this->data['searchCriteria'] = $this->ImportarQuadrosPorData($de, $para);
        } else if (is_numeric($id) and $id != null){
            $this->data['searchCriteria'] = $this->ImportarProdutoPorId($id);
        }
        $result = $this->magento->api->call('products', $this->data, 'GET');
        if (count($result->items)){
            foreach ($result->items as $item){
                $produto = Produtos::updateOrCreate(
                    ['id_magento' => $item->id],
                    [
                        'nome' => $item->name,
                        'id_magento' => $item->id,
                        'sku' => $item->sku,
                        'tipo' => $item->type_id,
                        'tipo_composicao' => $this->Attr('composicao',$item->custom_attributes),
                        'categorias_ids' => $this->Attr('category_ids',$item->custom_attributes),
                        'material' => $this->Attr('material',$item->custom_attributes),
                        'acabamento' => $this->Attr('moudura',$item->custom_attributes),
                        'tamanho' => $this->Attr('tamanho',$item->custom_attributes),
                        'url' => $this->Attr('url_key',$item->custom_attributes),
                        'op_conf' => $this->op_conf($item->extension_attributes),
                        'op_filhos' => $this->op_filhos($item->extension_attributes)
                    ]
                );
            }
       }
    }
    private function Attr (String $code, Array $array) :String {
        foreach ($array as $a){
            if ($code == $a->attribute_code){
                $valor = is_array($a->value) ? json_encode($a->value) : $a->value;
                return $valor;
            }
        }
        return '';
    }
    private function op_conf($ea){
        if (isset($ea->configurable_product_options)){
            return json_encode($ea->configurable_product_options);
        }
        return '';
    }
    private function op_filhos($ea){
        if (isset($ea->configurable_product_links)){
            return json_encode($ea->configurable_product_links);
        }
        return '';
    }
    public function ImportarQuadrosPorData($de, $para) :Array {
        $data = [
            'filterGroups' => [
                0 => [
                    'filters' => [
                        0 => [
                            'field' => 'category_id',
                            'value' => '125,124,141,278,123,147,219,169,166,235,126,171,157,142,170,173,172,279,165,229,116',
                            'condition_type' => 'in'
                        ],
                        1 => [
                            'field' => 'category_id',
                            'value' => '188,197,185,194,193,191,234,152,223,189,188,179,279,165,229,222,252',
                            'condition_type' => 'nin'
                        ]
                    ]
                ]
            ],
            //'pageSize' => 20,
            'currentPage' => 1
        ];
        $de = date_format(date_create($de), 'Y-m-d H:i:s');
        $para =   date_format(date_create($para), 'Y-m-d H:i:s');

        $data['filterGroups'][1]['filters'][] = [
                        'field' => 'created_at',
                        'value' => $de,
                        'condition_type' => 'from'
                ];
        $data['filterGroups'][2]['filters'][] = [
                        'field' => 'created_at',
                        'value' => $para,
                        'condition_type' => 'to'
        ];
        return $data;
    }
    public function ImportarProdutoPorId($id) :Array {
        $data = [
            'filterGroups' => [
                0 => [
                    'filters' => [
                        0 => [
                            'field' => 'entity_id',
                            'value' => $id,
                            'condition_type' => 'eq'
                        ]
                    ]
                ]
            ],
            'currentPage' => 1
        ];
        return $data;
    }

}
