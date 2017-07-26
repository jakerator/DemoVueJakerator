<?php

namespace AppBundle;


class JsonDataProvider
{
    /*
     * This class provides methods and features to work with source JSON, filter patients, sort and do other operations
     */


    /*
     * Transforms js-based Json string into PHP array and return as a result
     *
     * @return array Source Json as PHP Array
     *
     */

    public static function getJson()
    {
        $json_str='
                    {
                navigation: [
                    {
                        id: "AZ",
                        name: "sortation",
                        url: "patient-list",
                        title: "Patienten-Liste",
                        collection: "patient",
                        group: false,
                        filter: {status: 1},
						 filterBox: [
                            {type: "text" , value: ["name"]}
                        ]
                    },
                    {
                        id: "group",
                        name: "group",
                        url: "patient-list",
                        title: "Patienten-Liste",
                        collection: "patient",
                        group: true
                    },
                    {
                        id: "archive",
                        name: "archive",
                        url: "patient-list-archived",
                        title: "Liste der archivierten Patienten",
                        collection: "patient",
                        group: false,
                        filter: {status: 0}
                    }
                ],
                collections: {
                    patient: [
                        {id: 1, label : "Vier Testfall", group:  "Mende, Manuela", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 2,label : "Beata Brysz", group: "Ittri, Mulham", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 3,label: "Claus Nolte", group: "Ittri, Mulham", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 4,label: "Andrea Kuckuck", group:  "Mende, Manuela", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 5,label: "Frank Weigel", group:  "Mende, Manuela", status: 0, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 6,label: "Marie Meier", group: "Ittri, Mulham", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 7,label: "Heike Otto", group: "Ittri, Mulham", status: 0, details: {email: "test@test.de" , tel : "12sd34"}},
                        {id: 8,label: "Heike Otto2", group: "Ittri, Mulham", status: 0, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 9,label: "Heike Otto3", group: "Ittri, Mulham", status: 0, details: {email: "tedst@test.de" , tel : "12sdf34"}},
                        {id: 10,label: "Heike Otto4", group: "Ittri, Mulham", status: 0, details: {email: "tesdfst@test.de" , tel : "12f34"}},
                        {id: 11,label: "Heike Otto5", group: "Ittri, Mulham2", status: 0, details: {email: "tesdfst@test.de" , tel : "1234"}},
                        {id: 12,label: "Heike Otto6", group: "Ittri, Mulham2", status: 0, details: {email: "tessst@test.de" , tel : "12d34"}},
                        {id: 13,label: "Heike Otto7", group: "Ittri, Mulham", status: 0, details: {email: "tesdt@test.de" , tel : "1234"}},
                        {id: 14,label: "John Doe", group: "Ittri, Mulham", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 15,label: "Bill Gates", group: "Ittri, Mulham", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 16,label: "Vasiliy Pupkin", group: "Another Group", status: 1, details: {email: "test@test.de" , tel : "1234"}},
                        {id: 17,label: "Yetti", group: "Somegroup", status: 1, details: {email: "test@test.de" , tel : "1234"}}
                    ]
                }

            }
        ';

        /*
         * As the source Json string is JS-well-formed, it can not be parsed by PHP native json_decode 'as is'.
         * So we had to build special function to convert it to well-format PHP json
         *
        */

        $json_array=\AppBundle\JsonDataProvider::jsonDecode($json_str);

        /*
         * Return Source JSON as PHH Array
         */
        return $json_array;


    }

    /*
     * Convert JS Json to PHP-compliant JSON
     * @param string $string    Input JSON String
     * @param bool $assoc       Convert result to associative array
     * @param bool $fixNames    Fix param names
     * @return array The transformed array
     */

    public static function jsonDecode($string, $assoc=true, $fixNames=true){
        if(strpos($string, '(') === 0){
            $string = substr($string, 1, strlen($string) - 2); // remove outer ( and )
        }
        if($fixNames){
            $string = preg_replace("/(?<!\"|'|\w)([a-zA-Z0-9_]+?)(?!\"|'|\w)\s?:/", "\"$1\":", $string);
        }
        //echo $string;
        return json_decode($string, $assoc);
    }



    /*
     * Filters list of Patients
     * @param array $patients               Input array of Patients
     * @param string|bool $filterStatus     Filter by status
     * @param string|bool $filterText       Filter by text in label
     * @param string|bool $filterGroup      Filter by group
     * @return array The filtered array
     */

    public static function filterPatients($patients, $filterStatus=false,$filterText=false,$filterGroup=false ){

        $filtered_patients=[];

        foreach ((array)$patients as $patient)
        {
            if ($filterStatus!==false && (string)$patient['status']!=$filterStatus)
            {
                //skip item
            }
            elseif ($filterText && strlen($filterText) && !preg_match("/".$filterText."/i", $patient['label']))
            {
                //skip item
            }
            elseif ($filterGroup && strlen($filterGroup) && (string)$patient['group']!=$filterGroup)
            {
                //skip item
            }
            else
            {
                $filtered_patients[]=$patient;
            }
        }

        return $filtered_patients;

    }

    /*
     * Sort list of Patients alphabetically
     * @param array $patients     Input array of Patients
     *
     * @return array The filtered array
     */
    public static function sortPatients($patients)
    {
        $sorted=usort($patients, '\AppBundle\JsonDataProvider::cmp');
        return ($patients);
    }


    /*
     * Sercice function to compare strings (used for sorting)
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    public static function cmp($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }

}
