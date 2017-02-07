<?php

namespace NielsHoppe\RDFDAV\CardDAV;

use \Sabre\VObject;

use \NielsHoppe\RDFDAV\Constants;

class VCardBuilder {

    private $fn = "";
    private $nickname = "";

    /*
    Family Names (also known as surnames),
    Given Names,
    Additional Names,
    Honorific Prefixes,
    Honorific Suffixes
    */
    private $name = ["", "", "", "", ""];
    private $tel = [];
    private $email = [];
    private $url = [];

    private static $mappedLiterals = [
        Constants::NS_VCARD . "fn" => "FN",
        Constants::NS_VCARD . "nickname" => "NICKNAME",
        Constants::NS_VCARD . "hasTelephone" => "TEL",
        Constants::NS_VCARD . "hasEmail" => "EMAIL",
        Constants::NS_VCARD . "hasURL" => "URL"
    ];

    private static $mappedCompound = [
        Constants::NS_VCARD . "family-name" => "N",
        Constants::NS_VCARD . "given-name" => "N"
    ];

    private $card;

    public function __construct () {
        $this->card = new VObject\Component\VCard();
    }

    public function readFromRDF ($properties) {

        foreach ($properties as $prop => $values) {

            foreach ($values as $value) {

                $this->addFromRDF($prop, $value["value"]);
            }
        }
    }

    public function addFromRDF ($property, $value) {

        if (array_key_exists($property, self::$mappedLiterals)) {

            $this->card->add(self::$mappedLiterals[$property], $value);
        }
        else {

            switch ($property) {

            case Constants::NS_VCARD . "family-name":
                $this->name[0] = $value;
                $this->card->N = $this->name;
                break;

            case Constants::NS_VCARD . "given-name":
                $this->name[1] = $value;
                $this->card->N = $this->name;
                break;

            default:
                break;
            }
        }
    }

    public function getCard() { return $this->card; }
}
