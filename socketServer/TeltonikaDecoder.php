<?php
/**
 * Created by PhpStorm.
 * User: Alvaro
 * Date: 12/07/2018
 * Time: 19:01
 */

namespace app\socketServer;


use app\socketServer\Entities\AVLData;

interface TeltonikaDecoder
{
    public function getNumberOfElements() :int;
    public function getCodecType() :int;
    public function decodeAVLArrayData(string $hexDataOfElement) :AVLData;
    public function getArrayOfAllData() :array;
}