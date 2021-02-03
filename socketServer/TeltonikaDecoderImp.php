<?php
/**
 * Created by PhpStorm.
 * User: Alvaro
 * Date: 12/07/2018
 * Time: 19:47
 */

namespace app\socketServer;

use app\socketServer\Entities\AVLData;
use app\socketServer\Entities\GPSData;
use app\socketServer\Entities\IOData;
use app\socketServer\Entities\ImeiNumber;

class TeltonikaDecoderImp implements TeltonikaDecoder
{

    const HEX_DATA_LENGHT = 255;
    const HEX_DATA_HEADER = 20;

    const CODEC8 = 8;
    const CODEC7 = 7;
    const CODEC16 = 16;

    const TIMESTAMP_HEX_LENGTH = 16;
    const PRIORITY_HEX_LENGTH = 2;
    const LONGITUDE_HEX_LENGTH = 8;
    const LATITUDE_HEX_LENGTH = 8;
    const ALTITUDE_HEX_LENGTH = 4;
    const ANGLE_HEX_LENGTH = 4;
    const SATELLITES_HEX_LENGTH = 2;
    const SPEED_HEX_LENGTH = 4;

    const EVENTID_HEX_LENGTH = 2;
    const ELEMENTCOUNT_HEX_LENGTH = 2;
    const ID_HEX_LENGTH = 2;
    const VALUE_HEX_LENGTH = 2;
    const ELEMENT_COUNT_1B_HEX_LENGTH = 2;

    private $imei;
    private $dataFromDevice;
    private $AVLData;
    private $HexDataShift = 0;

    /**
     * TeltonikaDecoderImp constructor.
     * @param $dataFromDevice
     */
    public function __construct(string $dataFromDevice, $imei)
    {
        //echo "$dataFromDevice \n";
        $this->dataFromDevice = $dataFromDevice;
        //$this->dataFromDevice = "000000000000004308020000016B40D57B480100000000000000000000000000000001010101000000000000016B40D5C198010000000000000000000000000000000101010101000000020000252C";
        $this->imei = $imei;
        $this->AVLData = array();
    }


    public function getNumberOfElements(): int
    {
        $dataCountHex = substr($this->dataFromDevice,18,2);
        $dataCountDecimal = hexdec($dataCountHex);

        return $dataCountDecimal;
    }

    public function getCodecType(): int
    {
        $codecTypeHex = substr($this->dataFromDevice,16,2);
        $codecTypeDecimal = hexdec($codecTypeHex);

        return $codecTypeDecimal;
    }

    public function decodeAVLArrayData(string $hexDataOfElement) :AVLData
    {
        $codecType = $this->getCodecType();

        if($codecType == self::CODEC8) {
            return $this->codec8Decode($hexDataOfElement);
        }

    }

    public function getArrayOfAllData(): array
    {
        $AVLArray = array();

        $hexDataWithoutCRC = substr($this->dataFromDevice, 0, -8);
        $hexAVLDataArray = substr($hexDataWithoutCRC, self::HEX_DATA_HEADER);

        $dataCount = $this->getNumberOfElements();

        $startPosition = self::HEX_DATA_HEADER;

        for($i=0; $i<$dataCount; $i++) {
            $hexDataOfElement = substr($hexDataWithoutCRC, $startPosition);
            //
            //Decode and add to array of elements
            $AVLArray[] = $this->decodeAVLArrayData($hexDataOfElement);
            //
            $startPosition += $this->HexDataShift;
            //echo "start position $startPosition";
        }

        return $AVLArray;
    }

    private function codec8Decode(string $hexDataOfElement) :AVLData {

        
        $arrayElement = array();

        $AVLElement = new AVLData();

        $AVLElement->setImei($this->imei);
        //We only get first 10 characters to get timestamp up to seconds.
        $timestamp = substr(hexdec(substr($hexDataOfElement, 0, self::TIMESTAMP_HEX_LENGTH)), 0, 10);
        $dateTimeWithoutFormat = new \DateTime();
        $dateTimeWithoutFormat->setTimestamp(intval($timestamp));
        $dateTimeWithFormat =  $dateTimeWithoutFormat->format('Y-m-d H:i:s') . "\n";

        $AVLElement->setTimestamp($timestamp);
        $AVLElement->setDateTime($dateTimeWithFormat);

        $stringSplitter = self::TIMESTAMP_HEX_LENGTH;
        $priority = hexdec(substr($hexDataOfElement, $stringSplitter, self::PRIORITY_HEX_LENGTH));
        $AVLElement->setPriority($priority);
        $stringSplitter+= self::PRIORITY_HEX_LENGTH;
        $longitudeValueOnArrayTwoComplement = unpack("l", pack("l", hexdec(substr($hexDataOfElement, $stringSplitter, self::LONGITUDE_HEX_LENGTH))));
        $longitude = (float) (reset($longitudeValueOnArrayTwoComplement) / 10000000);
        $stringSplitter+= self::LONGITUDE_HEX_LENGTH;
        $latitudeValueOnArrayTwoComplement = unpack("l", pack("l", hexdec(substr($hexDataOfElement, $stringSplitter, self::LATITUDE_HEX_LENGTH))));
        $latitude = (float) (reset($latitudeValueOnArrayTwoComplement) / 10000000);
        $stringSplitter+= self::LATITUDE_HEX_LENGTH;
        $altitude = hexdec(substr($hexDataOfElement, $stringSplitter, self::ALTITUDE_HEX_LENGTH));
        $stringSplitter+= self::ALTITUDE_HEX_LENGTH;
        $angle = hexdec(substr($hexDataOfElement, $stringSplitter, self::ANGLE_HEX_LENGTH));
        $stringSplitter+= self::ANGLE_HEX_LENGTH;
        $satellites = hexdec(substr($hexDataOfElement, $stringSplitter, self::SATELLITES_HEX_LENGTH));
        $stringSplitter+= self::SATELLITES_HEX_LENGTH;
        $speed = hexdec(substr($hexDataOfElement, $stringSplitter, self::SPEED_HEX_LENGTH));
        $stringSplitter+= self::SPEED_HEX_LENGTH;
        // echo "I/O length" . hexdec(substr($hexDataOfElement, $stringSplitter, self::ELEMENTCOUNT_HEX_LENGTH)) . "\n";
        $GPSData = new GPSData($longitude, $latitude, $altitude, $angle, $satellites, $speed);

        $AVLElement->setGpsData($GPSData);
        //echo "\n splitter value $stringSplitter \n";
        $rested = substr($hexDataOfElement, self::TIMESTAMP_HEX_LENGTH);
        $eventID = hexdec(substr($hexDataOfElement, $stringSplitter, self::EVENTID_HEX_LENGTH));
        $stringSplitter+= self::EVENTID_HEX_LENGTH;
        $elementCount = hexdec(substr($hexDataOfElement, $stringSplitter, self::ELEMENTCOUNT_HEX_LENGTH));
        $stringSplitter+= self::ELEMENTCOUNT_HEX_LENGTH;
        //echo "event id $eventID \n";
        //echo "total i/o count $elementCount \n";
        
        //echo "rested $rested";

        /* I/O decode */
        $count_1B_IO = hexdec(substr($hexDataOfElement, $stringSplitter, 2));
        //echo "*1b count - $count_1B_IO \n";
        $stringSplitter+= 2; // move to 1 byte
        for($i = 0; $i < $count_1B_IO; $i++) {
            $ID = hexdec(substr($hexDataOfElement, $stringSplitter, self::ID_HEX_LENGTH));
            $stringSplitter+= self::ID_HEX_LENGTH;
            $value = hexdec(substr($hexDataOfElement, $stringSplitter, self::VALUE_HEX_LENGTH));
            $stringSplitter+= self::VALUE_HEX_LENGTH;
            $IOElements [] = new Entities\IOData($eventID, $elementCount, $ID, $value);
            //echo "\n{1b: ID: $ID, value: $value}\n";
        }

        $count_2B_IO = hexdec(substr($hexDataOfElement, $stringSplitter, 2));
        //echo "*2b count - $count_2B_IO \n";
        $stringSplitter+= 2; // move to 1 byte
        for($i = 0; $i < $count_2B_IO; $i++) {
            $ID = hexdec(substr($hexDataOfElement, $stringSplitter, 2));
            $stringSplitter+= 2;
            $value = hexdec(substr($hexDataOfElement, $stringSplitter, 4));
            $stringSplitter+= 4;
            $IOElements [] = new Entities\IOData($eventID, $elementCount, $ID, $value);
            //echo "\n{2b: ID: $ID, value: $value}\n";
        }

        $count_4B_IO = hexdec(substr($hexDataOfElement, $stringSplitter, 2));
        $stringSplitter+= 2; // move to 1 byte
        //echo "*4b count - $count_4B_IO \n";
        
        for($i = 0; $i < $count_4B_IO; $i++) {
            $ID = hexdec(substr($hexDataOfElement, $stringSplitter, self::ID_HEX_LENGTH));
            $stringSplitter+= self::ID_HEX_LENGTH;
            $value = hexdec(substr($hexDataOfElement, $stringSplitter, 8));
            $stringSplitter+= 8;
            $IOElements [] = new Entities\IOData($eventID, $elementCount, $ID, $value);
            //echo "\n{4b: ID: $ID, value: $value}\n";
        }

        $count_8B_IO = hexdec(substr($hexDataOfElement, $stringSplitter, 2));
        //echo "*8b count - $count_4B_IO \n";
        $stringSplitter+= 2; // move to 1 byte
        for($i = 0; $i < $count_8B_IO; $i++) {
            $ID = hexdec(substr($hexDataOfElement, $stringSplitter, self::ID_HEX_LENGTH));
            $stringSplitter+= self::ID_HEX_LENGTH;
            $value = hexdec(substr($hexDataOfElement, $stringSplitter, 16));
            $stringSplitter+= 16;
            $IOElements [] = new Entities\IOData($eventID, $elementCount, $ID, $value);
            //echo "\n{8b: ID: $ID, value: $value}\n";
        }
        $this->HexDataShift = $stringSplitter;
        //echo "shift $stringSplitter";
        

        $AVLElement->setIOData($IOElements);

        return $AVLElement;

    }

    private function convert($hex)
    {
        $dec = hexdec($hex);
        return ($dec < 0x7fffffff) ? $dec
            : 0 - (0xffffffff - $dec);
    }
}