<?php
namespace App\Ajax;

use Tk\Request;
/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Product
{

    /**
     * @param Request $request
     * @return \Tk\Response
     * @throws \Exception
     */
    public function doFindFiltered(Request $request)
    {
        $config = \App\Config::getInstance();

//        try {
//            $filter = $request->all();
//            // TODO: check for an empty filter, would return all records??
//            $data = \App\Db\ProductMap::create()->findFiltered($filter, \Tk\Db\Tool::create('name'))->toArray();
//            return \Tk\ResponseJson::createJson($data);
//        } catch (\Exception $e) {
//            return \Tk\ResponseJson::createJson(array(
//                'status' => 'err',
//                'message' => $e->getMessage()
//            ), \Tk\Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
    }

    /**
     * @param Request $request
     * @return \Tk\Response
     * @throws \Exception
     */
    public function doFindByName(Request $request)
    {
        $config = \App\Config::getInstance();

        try {
            $filter = $request->all();
            $data = [];
            if (count($filter)) {
                if (!empty($filter['term'])) $filter ['keywords'] = $filter['term'];
                $list = \App\Db\ProductMap::create()->findFiltered($filter, \Tk\Db\Tool::create('name'));
                foreach ($list as $product) {
                    $data[] = [
                        'id' => $product->getId(),
                        'value' => $product->getName(),
                        'label' => $product->getName(),
                        'code' => $product->getCode(),
                        'price' => $product->getPrice()->toFloatString()
                    ];
                }
            }
            return \Tk\ResponseJson::createJson($data);
        } catch (\Exception $e) {
            return \Tk\ResponseJson::createJson(array(
                'status' => 'err',
                'message' => $e->getMessage()
            ), \Tk\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }





}