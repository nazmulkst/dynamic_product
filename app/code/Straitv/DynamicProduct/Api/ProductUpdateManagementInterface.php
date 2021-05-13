<?php
namespace Straitv\DynamicProduct\Api;

interface ProductUpdateManagementInterface
{
    /**
     * Updates the specified products in item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    public function updateProduct($products);
}
