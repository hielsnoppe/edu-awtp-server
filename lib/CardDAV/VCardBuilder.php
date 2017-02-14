<?php

namespace NielsHoppe\RDFDAV\CardDAV;

use \Sabre\VObject;

use \NielsHoppe\RDFDAV\Constants;

/**
 * @see http://sabre.io/vobject/vcard/
 */
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

    /**
     * Used by addFromRDF()
     */
    private static $mappedLiterals = [
        Constants::NS_VCARD . "fn" => "FN",
        Constants::NS_VCARD . "nickname" => "NICKNAME",
        #Constants::NS_VCARD . "hasTelephone" => "TEL", // no direct mapping, possibly needs clean up
        #Constants::NS_VCARD . "hasEmail" => "EMAIL", // no direct mapping, possibly needs clean up
        Constants::NS_VCARD . "hasURL" => "URL",
        Constants::NS_VCARD . "hasPhoto" => "PHOTO"
    ];

    /**
     * Informative
     */
    private static $mappedCompound = [
        Constants::NS_VCARD . "family-name" => ["N", 0],
        Constants::NS_VCARD . "given-name" => ["N", 1],
        Constants::NS_VCARD . "additional-name" => ["N", 2],
        Constants::NS_VCARD . "honorific-prefix" => ["N", 3],
        Constants::NS_VCARD . "honorific-suffix" => ["N", 4]
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

            case Constants::NS_VCARD . "email":
            case Constants::NS_VCARD . "hasEmail":
                $value = preg_replace('|^mailto:|', '', $value);
                $this->card->add('EMAIL', $value);
                break;

            case Constants::NS_VCARD . "tel":
            case Constants::NS_VCARD . "hasTelephone":
                $value = preg_replace('|^tel:|', '', $value);
                $this->card->add('TEL', $value);
                break;

            case Constants::NS_VCARD . "family-name":
                $this->name[0] = $value;
                $this->card->N = $this->name;
                break;

            case Constants::NS_VCARD . "given-name":
                $this->name[1] = $value;
                $this->card->N = $this->name;
                break;

            case Constants::NS_VCARD . "additional-name":
                $this->name[2] = $value;
                $this->card->N = $this->name;
                break;

            case Constants::NS_VCARD . "honorific-prefix":
                $this->name[3] = $value;
                $this->card->N = $this->name;
                break;

            case Constants::NS_VCARD . "honorific-suffix":
                $this->name[4] = $value;
                $this->card->N = $this->name;
                break;

            default:
                break;
            }
        }
    }

    public function getCard () {

        if (empty($this->card->FN)) {

            $fn = vsprintf('%4$s %2$s %1$s', $this->name);
            $fn = trim(preg_replace('|\s+|', ' ', $fn));

            $this->card->add('FN', $fn);
        }

        return $this->card;
    }
}
