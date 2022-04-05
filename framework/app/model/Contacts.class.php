<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class Contacts extends TRecord
{
    const TABLENAME = 'contacts';
    const PRIMARYKEY = 'Id';
    const IDPOLICY = 'max';

    private $students;

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('AreaCode');
        parent::addAttribute('PhoneNumber');
        parent::addAttribute('IdStudents');
        
    }

    // atribui um contato $param,
    // $students objeto da classe Student
    public function set_students(Students $students)
    {
        $this->students = $students; // armazena o objeto
        $this->IdStudents = $students->Id;
    }

    // retorna o contato associado
    public function get_students()
    {
        if (empty($this->students))
        {
            $this->students = new Students($this->IdStudents);
        }

        return $this->students;
    }

    public function get_students_name( $id = NULL)
    {
        if ( empty( $this->students ) )
		{
			$this->students = new Students( $this->IdStudents );
		}
		return $this->students->Name;
    }

    public static function getStudentName( $IdStudents = NULL )
	{
		$nameStudent = '';
		
		try
		{
			TTransaction::open( 'bancoMysql' );
			$repositoryContact = new TRepository( 'Students' );
			
			$filterStudent = new TFilter( 'Id', '=', $IdStudents );
			
			$criteriaContact = new TCriteria;
			$criteriaContact->add( $filterStudent);
			
			$objects = $repositoryContact->load( $criteriaContact );
			
			if ( $objects )
			{
				foreach ( $objects as $object )
				{
					$nameStudent = $object->Name;
				}
			}
			
			$criteriaContact->resetProperties();
			$count = $repositoryContact->count( $criteriaContact );
			
			TTransaction::close();
		}
		catch ( Exception $e )
		{
			new TMessage( 'error', $e->getMessage() );
			TTransaction::rollback();
		}
		
		return $nameStudent;
	}

}