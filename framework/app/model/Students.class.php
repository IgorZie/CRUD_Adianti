<?php

use Adianti\Database\TRecord;

class Students extends TRecord
{
    const TABLENAME = 'students';
    const PRIMARYKEY = 'Id';
    const IDPOLICY = 'max';

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('Name');
        parent::addAttribute('Identification');
        parent::addAttribute('Email');
        parent::addAttribute('RecordDate');
        parent::addAttribute('ZipCode');
        parent::addAttribute('ChangeDate');
        parent::addAttribute('State');
        
    }

}