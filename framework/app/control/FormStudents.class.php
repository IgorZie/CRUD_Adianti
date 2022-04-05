<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Validator\TCPFValidator;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TMaxLengthValidator;
use Adianti\Validator\TMinLengthValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TInputDialog;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;


class FormStudents extends TPage
{
    private $form, $datagrid, $formToContact;

    // use Adianti\Base\AdiantiStandardListTrait;
    function __construct()
    {
        parent::__construct();

        // $this->setDatabase('bancoMysql');
        // $this->setActiveRecord('Contacts');

        $this->form = new BootstrapFormBuilder('form_Student');
        $this->form->setFormTitle('<b style="font-size: 20px">Cadastro de Estudantes</b>');
        $this->form->setFieldSizes('100%');
        $this->form->generateAria();

        $id = new THidden('Id');
        $name = new TEntry('Name');
        $identification = new TEntry('Identification');
        $email = new TEntry('Email');
        $cep = new TEntry('ZipCode');
        $state = new TCombo('State');
        $recorddate = new TEntry('RecordDate');
        // $recorddate->setMask( date('dd/mm/yyyy hh:ii') );
        $recorddate->setValue(date('d/m/Y H:i'));
        // $recorddate->setDatabaseMask( 'yyyy-mm-dd hh:ii:ss' );
        $changedate = new TEntry('ChangeDate');
        $changedate->setValue("");
        // $changedate->setMask( 'dd/mm/yyyy hh:ii' );
        // $changedate->setDatabaseMask( 'yyyy-mm-dd hh:ii:ss' );


        $recorddate->setEditable(FALSE);
        $changedate->setEditable(FALSE);
        // $id->setEditable(FALSE);

        $name->setSize('50%');
        $identification->setMask('999.999.999-99', true);
        $identification->setSize('50%');
        $email->setSize('50%');
        $cep->setSize('50%');
        $cep->setMask('99999-999', true);
        $state->setSize('50%');
        $state->addItems(['SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'RJ' => 'Rio de Janeiro', 'RS' => 'Rio Grande do Sul', 'BA' => 'Bahia', 'PR' => 'Paraná']);

        // $this->form->addFields([$id]);
        // $this->form->addFields( [new TLabel('Nome')], [$name]);
        // $this->form->addFields( [new TLabel('CPF')], [$identification]);
        // $this->form->addFields( [new TLabel('Email')], [$email]);
        // $this->form->addFields( [new TLabel('CEP')], [$cep]);
        // $this->form->addFields( [new TLabel('UF')], [$state]);
        $this->form->addFields([$id]);
        $row = $this->form->addFields([new TLabel('Nome'), $name], [new TLabel('CPF'), $identification]);
        $row->layout = ['col-sm-6', 'col-sm-6'];
        $row = $this->form->addFields([new TLabel('Email'), $email], [new TLabel('CEP'), $cep]);
        $row->layout = ['col-sm-6', 'col-sm-6'];
        $row = $this->form->addFields([new TLabel('UF'), $state], [new TLabel('Data de Cadastro'), $recorddate], [new TLabel('Última Alteração'), $changedate]);
        $row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-2', 'col-sm-2'];


        $name->addValidation('Nome', new TMinLengthValidator, array(3));
        $identification->addValidation('CPF', new TCPFValidator);
        $email->placeholder = 'name@exemplo.com';
        $email->addValidation('Email', new TEmailValidator);

        $this->form->addAction('Send', new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addActionLink('Cancelar', new TAction(array('DatagridStudents', 'onReload')), 'far:trash-alt red');
        $this->form->addAction('Novo Telefone', new TAction(array($this, 'onToContact')), 'fa:plus green');

        // $Contact = new TAction( [ 'RelationContactStudent', 'onToContact' ], [ 'key' => '{Id}' ] );


        // Criação do datagrid dos telefones
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        // $this->setDatabase('bancoMySql');
        // $this->setActiveRecord('Contacts');

        $idContact   = new TDataGridColumn('Id', 'Id', 'center', '10%');
        $ddd         = new TDataGridColumn('AreaCode', 'DDD', 'center', '50%');
        $telefone    = new TDataGridColumn('PhoneNumber', 'Telefone', '50%');

        $this->datagrid->addColumn($idContact);
        $this->datagrid->addColumn($ddd);
        $this->datagrid->addColumn($telefone);

        // $Edit = new TDataGridAction(['FormStudents', 'onEdit'],   ['key' => '{Id}']);
        $Delete = new TDataGridAction([$this, 'onConfirmDelete'], ['Id' => '{Id}']);

        // $this->datagrid->addAction($Edit, 'Editar', 'fa:edit blue');
        $this->datagrid->addAction($Delete, 'Delete', 'far:trash-alt red');

        $this->datagrid->createModel();

        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add(TPanelGroup::pack('', $this->form, $this->datagrid));

        parent::add($vbox);
    }

    public function onToContact($param)
    {
        $data = $this->form->getData();
        // $this->form->getData($data);

        if ($data->Id != "") {

            $this->formToContact = new BootstrapFormBuilder('to_Contact_form');
            $this->formToContact->setFieldSizes('100%');
            $this->formToContact->generateAria();

            $StudentId = new THidden('Id');
            $StudentId->setValue($param['Id']);
            $NameStudent = new TLabel(Contacts::getStudentName($param['Id']));
            $NameStudent->setFontStyle('b');

            $areacode = new TEntry('DDD');
            $areacode->addValidation('DDD', new TMaxLengthValidator, array(2));
            $areacode->addValidation('DDD', new TMinLengthValidator, array(2));
            $areacode->addValidation('DDD', new TRequiredValidator);
            $phone = new TEntry('Telefone');
            $phone->setMask('99999-9999', TRUE);
            // $phone->addValidation('Telefone', new TMinLengthValidator, array(9));
            // $phone->addValidation('Telefone', new TMaxLengthValidator, array(9));
            // $phone->addValidation('Telefone', new TRequiredValidator);

            // $rowToSchedule = $formToSchedule->addFields( [ $ClientIdSchedule, $ClientNameSchedule ] );
            $rowToContact = $this->formToContact->addFields([$StudentId]);
            $rowToContact = $this->formToContact->addFields([$NameStudent]);
            $rowToContact->layout = ['col-sm-12'];
            $rowToContact = $this->formToContact->addFields([new TLabel('DDD'), $areacode], [new TLabel('Telefone'), $phone]);
            $rowToContact->layout = ['col-sm-2', 'col-sm-8'];
            // $rowToContact = $formToContact->addFields( [ new TLabel( 'Telefone' ), $phone ]);
            // $rowToContact->layout = [ 'col-sm-12' ];

            $this->formToContact->addAction('Salvar', new TAction([__CLASS__, 'onSaveContact']), 'fa:save green');
            $this->formToContact->addAction('Cancelar', new TAction(['FormStudents', 'onEdit']), 'far:trash-alt red');

            new TInputDialog('Novo Telefone', $this->formToContact);
        } else {
            new TMessage('info', 'Cadastre o estudante ou selecione um para adicionar um telefone');
        }
    }

    public static function onCancelContact($param)
    {
        //
    }

    function onCarregar($param)
    {   
        // var_dump($param); die;
        // $id = $param['Id'];
        try {

            $id = $param['Id'];
            
            if (isset($param['Id'])) {
                TTransaction::open('bancoMysql');

                // $objectStudent = new TRepository('Students');
                // $criteriaStudent = new TCriteria;
                // $criteriaStudent->add(new TFilter('Id', '=', $key));

                // $object = $objectStudent->load($criteriaStudent);
                // $this->form->onReload($object);

                $objectContact = new TRepository('Contacts');
                $criteriaContacts = new TCriteria;
                $criteriaContacts->add(new TFilter('IdStudents', '=', $id));

                $contacts = $objectContact->load($criteriaContacts);
                $this->datagrid->clear();
                foreach ($contacts as $Contact) {
                    $this->datagrid->addItem($Contact);
                    // var_dump($Contact);
                }
                
                // $this->onEdit($param);
                TTransaction::close();
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }



    function onEdit($param)
    {
        // var_dump($param); die;
        try {

            if (isset($param['Id'])) {
                $key = $param['Id'];

                TTransaction::open('bancoMysql');

                $objectStudent = new Students($key);
                // $objectStudent = new Students;
                // $objectStudent = new TRepository('Students');
                $criteriaContacts = new TCriteria;
                $criteriaContacts->add(new TFilter('Id', '=', $key));
                $this->form->setData($objectStudent);

                // $criteria = new TCriteria;
                // $criteria->add(new TFilter('IdStudents', '=', $key));
                // $repository = new TRepository('Students');
                // $this->form->setData($criteria);

                // 

                //         $contacts = $objectContact->load($criteriaContacts);
                //         $this->datagrid->clear();
                //         foreach ($contacts as $Contact) {
                //             $this->datagrid->addItem($Contact);
                //         }
                $this->onCarregar($param);
                TTransaction::close();                
            } else {
                $this->form->clear(true);
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSaveContact($param)
    {

        $IdStudent = $param['Id'];
        $areacode = $param['DDD'];
        $phone = $param["Telefone"];

        try {


            TTransaction::open('bancoMysql');

            $object = new Contacts;
            $object->AreaCode = $areacode;
            $object->PhoneNumber = str_replace("-", "", $phone);
            $object->IdStudents = $IdStudent;
            $object->store();

            new TMessage('info', 'Telefone cadastrado com sucesso');
            TTransaction::close();

            $this->onEdit($param);
            // AdiantiCoreApplication::loadPage('DatagridStudents', 'onReload');

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSave($param)
    {
        try {

            $data = $this->form->getData();
            if (empty($data->Id)) {
                $data->RecordDate = date('Y/m/d H:i:s');
            } else {
                $data->ChangeDate = date('Y/m/d H:i:s');
            }
            $this->form->validate();

            TTransaction::open('bancoMysql');

            $object = new Students;
            $object->fromArray((array) $data);
            // $object->Id = $data->Id;
            // $object->Name = $data->Name;
            // $object->Identification = $data->Identification;
            // $object->Email = $data->Identification;
            // $object->RecordDate = $data->RecordDate;
            // $object->ZipCode = $data->Cep;
            // $object->ChangeDate = $data->ChangeDate;
            // $object->State = $data->State;
            $object->store();

            // $this->form->setData( $object );
            // $this->fireEvents( $object );

            // new TMessage('info', 'Estudante cadastrado com sucesso');
            TToast::show('success', 'Registro salvo', 'top center', 'fa:check-circle-o');
            TTransaction::close();
            AdiantiCoreApplication::loadPage('DatagridStudents', 'onReload');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $this->form->setData($this->form->getData());
            TTransaction::rollback();
        }
    }

    function onClear($param)
    {
        $this->form->clear(TRUE);
    }


    public function onDelete($param)
    {
        $id = $param['Id'];

        try {

            TTransaction::open('bancoMysql');
            $contact = new Contacts($id);
            $contact->delete();

            // TToast::show('info', 'Estudante deletado com sucesso', 'top right', 'far:check-circle');
            new TMessage('info', 'Telefone deletado com sucesso');
            TTransaction::close();
            $this->onEdit($param);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

        $this->onEdit($param);
    }

    public function onConfirmDelete($param)
    {   
        $parameter = $param['Id'];

        // TTransaction::open('bancoMysql');
        // $contact = new Contacts($parameter);
        // var_dump($param);

        $action3 = new TAction(array($this, 'onDelete'));

        $action3->setParameter('Id', $parameter);

        new TQuestion('Deseja deletar?', $action3);


        if (isset($action3)) {
            $this->onEdit($param);
        }

        // $this->onReload($param);
    }
}
