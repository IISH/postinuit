<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 30-8-2017
 * Time: 11:16
 */

require_once "./classes/post.inc.php";
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    protected $keys;
    protected $values;
    protected $post;

    public function setUp(){
        $this->keys = [
            'ID',
            'in_out',
            'kenmerk',
            'date',
            'their_name',
            'their_organisation',
            'our_loginname',
            'our_name',
            'our_institute',
            'our_department',
            'type_of_document',
            'subject',
            'remarks',
            'registered_by',
            'number_of_files'
        ];
        $this->values = [
            1,
            'in',
            17999,
            '2199-01-01',
            'Bill Gates',
            'Microsoft',
            'FirstnameL',
            'Firstname Lastname',
            'IISG',
            'DI',
            2,
            'Job offer',
            'Come work for us!',
            'Igor van der Bom',
            5
        ];
        $this->post = new Post(array_combine($this->keys, $this->values));
    }

    public function testCreationOfPostIsNotEmpty(){
        $this->assertNotEmpty($this->post);
    }

    public function testIdOfPostEquals1(){
        $this->assertEquals(1, $this->post->getId());
    }

    public function testInOutOfPostEqualsIn(){
        $this->assertEquals('in', $this->post->getInOut());
    }

    public function testKenmerkOfPostEquals17999(){
        $this->assertEquals(17999, $this->post->getKenmerk());
    }

    public function testDateOfPostEquals21990101(){
        $this->assertEquals('2199-01-01', $this->post->getDate());
    }

    public function testTheirNameOfPostEqualsBillGates(){
        $this->assertEquals('Bill Gates', $this->post->getTheirName());
    }

    public function testTheirOrganisationOfPostEqualsMicrosoft(){
        $this->assertEquals('Microsoft', $this->post->getTheirOrganisation());
    }

    public function testOurNameOfPostEqualsFirstnameLastname(){
        $this->assertEquals('Firstname Lastname', $this->post->getOurName());
    }

    public function testOurInstituteFromPostNotEqualsKnaw(){
        $this->assertNotEquals('KNAW', $this->post->getOurOrganisation());
    }

    public function testOurDepartmentFromPostEqualsIisg(){
        $this->assertEquals('DI', $this->post->getOurDepartment());
    }

    public function testTypeOfDocumentOfPostEquals2(){
        $this->assertEquals(2, $this->post->getTypeOfDocument());
    }

    public function testSubjectOfPostEqualsJobOffer(){
        $this->assertEquals('Job offer', $this->post->getSubject());
    }

    public function testRemarksOfPostEqualsComeWorkForUs(){
        $this->assertEquals('Come work for us!', $this->post->getRemarks());
    }

    public function testRegisteredByOfPostEqualsIgorVanDerBom(){
        $this->assertEquals('Igor van der Bom', $this->post->getRegisteredBy());
    }

    public function testNumberOfFilesOfPostEqualsFive(){
        $this->assertEquals(5, $this->post->getNumberOfFiles());
    }

    public function testOurLoginNameOfPostEqualsFirstnameL(){
        $this->assertEquals('FirstnameL', $this->post->getOurLoginname());
    }
}