<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanel;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridActionGroup;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class DatagridStudents extends TPage
{
    private $form, $datagrid;

    use Adianti\Base\AdiantiStandardListTrait;

    function __construct()
    {
        parent::__construct();

        $this->setDatabase('bancoMysql');
        $this->setActiveRecord('Students');
        $this->addFilterField('Name', 'like', 'Name');
        $this->addFilterField('Identification', 'like', 'CPF');
        $this->setDefaultOrder('Id', 'asc');

        $this->form = new BootstrapFormBuilder('form_search_client');
        $this->form->setFormTitle('<b style="font-size: 20px">Estudantes</b>');
        $this->form->setFieldSizes('100%');
        $this->form->generateAria();

        $nameFilter = new TEntry('Name');
        $cpfFilter = new TEntry('CPF');
        $cpfFilter->setMask('999.999.999-99', TRUE);

        // $Students_filter_data = $_SESSION['sample']['Students_filter_data'];
        // if (isset($_SESSION['sample']['Students_filter_data'])) {
        //     $nameFilter->setValue($Students_filter_data->Name);
        // }
        // if (isset($_SESSION['sample']['Students_filter_data'])) {
        //     $cpfFilter->setValue($Students_filter_data->CPF);
        // }

        $row = $this->form->addFields(
            [new TLabel('Name'), $nameFilter],
            [new TLabel('CPF'), $cpfFilter]
        );
        $row->layout = ['col-sm-3', 'col-sm-3'];

        $this->form->addAction('Filtrar', new TAction(array($this, 'onSearch')), 'fa:search blue');
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addActionLink('Novo', new TAction(['FormStudents', 'onClear']), 'fa:plus green');


        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        // $this->datagrid->setHeight(300);
        // $this->datagrid->makeScrollable();

        $id             = new TDataGridColumn('Id', 'Id', 'center', '5%');
        $name           = new TDataGridColumn('Name', 'Name', 'left', '30%');
        $identification = new TDataGridColumn('Identification', 'CPF', 'left', '10%');
        $email          = new TDataGridColumn('Email', 'Email', 'left', '20%');
        $recordDate     = new TDataGridColumn('RecordDate', 'Data de Cadastro', 'left', '20%');
        $zipCode        = new TDataGridColumn('ZipCode', 'CEP', 'left', '10%');
        $changeDate     = new TDataGridColumn('ChangeDate', 'Alteração Cadastro', 'left', '20%');
        $state          = new TDataGridColumn('State', 'UF', 'left', '10%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($identification);
        $this->datagrid->addColumn($email);
        $this->datagrid->addColumn($recordDate);
        $this->datagrid->addColumn($changeDate);
        $this->datagrid->addColumn($zipCode);
        $this->datagrid->addColumn($state);

        $Edit = new TDataGridAction(['FormStudents', 'onEdit'],   ['Id' => '{Id}']);
        $Delete = new TDataGridAction([$this, 'onConfirmDelete'], ['Id' => '{Id}']);
        $Contact = new TDataGridAction(['FormStudents', 'onToContact'], ['key' => '{Id}']);

        $this->datagrid->addAction($Edit, 'Editar', 'fa:edit blue');
        $this->datagrid->addAction($Delete, 'Delete', 'far:trash-alt red');
        $this->datagrid->addAction($Contact, 'Telefones', 'fa:plus green');

        $this->datagrid->createModel();
        
        // $input_search = new TEntry('input_search');
        // $input_search->placeholder = _t('Search');
        // $input_search->setSize('100%');
        // $this->datagrid->enableSearch($input_search, 'Id, Name, CPF, Email, Data de Cadastro, CEP, Alteração de Cadastro, UF');

        $panel = new TPanelGroup;
        // $panel->addHeaderWidget($input_search);
        // $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter('CRUD com Adianti <br> &copy Igor Zielosko ' . date('Y'));

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        // $vbox->add(TPanelGroup::pack('<b style="font-size: 20px">Estudantes Cadastrados</b>', $this->datagrid));
        $vbox->add(TPanelGroup::pack('', $this->form, $this->datagrid));
        $vbox->add($panel);

        parent::add($vbox);
    }

    /*function onReload()
    {

        /*try {
            TTransaction::open('bancoMysql');

            // $students = Students::select('Id', 'Name' , 'Identification', 'Email', 'RecordDate', 'ZipCode', 'ChangeDate', 'State')->load();
            $repository = new TRepository('Students');

            $criteriaStudents = new TCriteria;

            if (empty($param['order'])) {
                $param['order'] = 'Id';
                $param['direction'] = 'asc';
            }

            $criteriaStudents->setProperties($param);

            if (TSession::getValue('Name_Student_filter')) {
                $criteriaStudents->add(TSession::getValue('Name_Student_filter'));
            }

            $students = $repository->load($criteriaStudents);
            $this->datagrid->clear();

            foreach ($students as $Student) {
                $this->datagrid->addItem($Student);
            }


            $criteriaStudents->resetProperties();
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }*/

    public function onDelete($param)
    {
        $id = $param['Id'];

        try {

            TTransaction::open('bancoMysql');

            $criteria = new TCriteria;
            $criteria->add(new TFilter('IdStudents', '=', $id));
            $repository = new TRepository('Contacts');
            $repository->delete($criteria);
            
            $student = new Students($id);
            $student->delete();

            // TToast::show('info', 'Estudante deletado com sucesso', 'top right', 'far:check-circle');
            new TMessage('info', 'Estudante deletado com sucesso');
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

        $this->onReload();
    }

    public function onConfirmDelete($param)
    {
        $parameter = $param['Id'];

        $action3 = new TAction(array($this, 'onDelete'));

        $action3->setParameter('Id', $parameter);

        new TQuestion('Deseja deletar?', $action3);
    }

    /*function onSearch()
    {
        
    /*try {
            $data = $this->form->getData();
            $this->form->getData($data);

            $nameFilterName = $data->Name;
            // $cpfFilterNumeric = $data->CPF;

            if (isset($data->Name)) {

                $nameStudentFilter = Students::getStudentName($nameFilterName);

                if ($nameFilterName != "") {

                    $filter = new TFilter('Name', 'like', "%$nameStudentFilter%");

                    TSession::setValue('Name_Student_filter', $filter);
                    TSession::setValue('Name_Student_value', $data->Name);
                } else {
                    TToast::show('warning', 'Nenhum registro encontrado', 'top center', 'fa:check-circle-o');
                    TSession::setValue('Name_Student_filter', []);
                    TSession::setValue('Name_Student_value', '');
                }
            } else {
                TSession::setValue('Name_Student_filter', []);
                TSession::setValue('Name_Student_value', '');
            }

            // $param = array();
            // $param['offset'] = 0;
            // $param['first_page'] = 1;
            // $this->onReload( $param );

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }*/

    function onClear($param)
    {
        // $students_filter_data = $_SESSION['sample']['Students_filter_data'];
        // TToast::show('info', print_r($students_filter_data->Name), 'top right', 'far:check-circle');
        $this->form->clear(TRUE);
        $this->onSearch();
    }


    function show()
    {
        $this->onReload();
        parent::show();

        if (isset($_SESSION['sample'])) {
            if (isset($_SESSION['sample']['Students_filter_data'])) {
                $students_filter_data = $_SESSION['sample']['Students_filter_data'];

                $obj = new StdClass;
                $obj->Name = $students_filter_data->Name;

                TForm::sendData('form_search_client', $obj);
            }
        }
    }
}
