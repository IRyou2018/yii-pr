<?php

namespace frontend\models;

use yii\base\Model;

class Upload extends Model {
    
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls, xlsx, csv']
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'Upload File'
        ];
    }

    public function validateGroupTemplateFormat($data)
    {
        $paKeys = ["Group Name", "Matriculation Number", "First Name", "Last Name", "Email"];

        $dataKeys = array_keys($data[0]);

        return (
            is_array($paKeys) 
            && is_array($dataKeys) 
            && count($paKeys) == count($dataKeys) 
            && array_diff($paKeys, $dataKeys) === array_diff($dataKeys, $paKeys)
        );
    }

    public function validateInputContents($data)
    {
        $valid = true;

        foreach($data as $input){
            $inputValue = array_values($input);
            // echo '<pre>';
            // print_r($inputValue);
            // echo '</pre>';

            if(empty($inputValue[0]) && empty($inputValue[1]) && empty($inputValue[2]) && empty($inputValue[3]) && empty($inputValue[4])) {
                continue;
                // echo '<pre>';
                // print_r("true");
                // echo '</pre>';
            } else if (empty($inputValue[0]) || empty($inputValue[1]) || empty($inputValue[2]) || empty($inputValue[3]) || empty($inputValue[4])) {
                // echo '<pre>';
                // print_r("false");
                // echo '</pre>';
                $valid = false;
                break;
            }
        }
        // die();

        return $valid;
    }
}