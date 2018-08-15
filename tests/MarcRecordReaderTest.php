<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcReader\MarcReader;
use Umlts\MarcReader\MarcRecordReader;

/**
 * @covers Umlts\MarcReader\MarcRecordReader
 */
final class MarcRecordReaderTest extends TestCase {

    protected $record;

    protected function setUp() {
        $mr = new MarcReader( __DIR__ . '/data/cattle.mrc' );
        $this->record = $mr->nextRaw();
    }

    public function testCanBeCreated() {
        $mrr = new MarcRecordReader();
        $this->assertInstanceOf( MarcRecordReader::class, $mrr );
    }

    public function testGetLeader() {
        $mrr = new MarcRecordReader();
        $leader = $mrr->getLeader( $this->record );
        $this->assertEquals( $leader, '01732cam a2200433   4500' );
    }

    public function testGetBaseAddress() {
        $mrr = new MarcRecordReader();
        $base_address = $mrr->getBaseAddress( $this->record );
        $this->assertEquals( $base_address, 433 );
    }

    public function testGetDirectory() {
        $mrr = new MarcRecordReader();
        $dir = $mrr->getDirectory( $this->record );
        $this->assertEquals( count( $dir ), 34 );
    }

    public function testGetEntryAsAssocArray() {
        $mrr = new MarcRecordReader();
        $dir = $mrr->getDirectory( $this->record );
        $entry_array = $mrr->getEntryAsAssocArray( $dir[1] );
        $this->assertEquals(
            $entry_array,
            [ 
                'tag' => '003',
                'length' => 6,
                'position' => 13,
            ]
        );
    }

    public function testGet001() {
        $mrr = new MarcRecordReader();
        $f001 = $mrr->get001( $this->record );
        $this->assertEquals( $f001, 'crle00532681' );

        // Check exception
        $mr = new MarcReader( __DIR__ . '/data/no_001.mrc' );
        $this->expectException( \RuntimeException::class );
        $f001 = $mrr->get001( $mr->nextRaw() );
    }

    public function testGetControlFields() {
        $mrr = new MarcRecordReader();
        $field = $mrr->getControlFields( '007', $this->record );
        $this->assertEquals( count( $field ), 3 );
        $this->assertEquals(
            $field,
            [
                'cr                                          ',
                'cr bn||||||abp                              ',
                'cr bn||||||cda                              ',
            ]
        );

        // Check 001
        $mrr = new MarcRecordReader();
        $f001 = $mrr->getControlFields( '001', $this->record );
        $this->assertEquals( $f001, ['crle00532681'] );

        // Check exception
        $this->expectException( \InvalidArgumentException::class );
        $field = $mrr->getControlFields( 'abc', $this->record );
    }

    public function testGetDataFields() {

        $mrr = new MarcRecordReader();
        $field = $mrr->getDataFields( '650', $this->record );
        $this->assertEquals( count( $field ), 2 );

        $field_array =  array_map( [ $mrr, 'getDataFieldAsAssocArray' ], $field );
        $expected_array = [
            [
                'ind1' => ' ',
                'ind2' => '0',
                'subfields' => [
                    [ 'tag' => 'a', 'content' => 'Castration.', ],
                ],
            ],
            [
                'ind1' => ' ',
                'ind2' => '0',
                'subfields' => [
                    [ 'tag' => 'a', 'content' => 'Cattle', ],
                    [ 'tag' => 'x', 'content' => 'Physiology.', ]
                ],
            ]
        ];

        $this->assertEquals( $field_array, $expected_array );

        // Check exception
        $this->expectException( \InvalidArgumentException::class );
        $field = $mrr->getDataFields( 'abc', $this->record );
      
    }

    public function testGetDataFieldsEncoding() {
        $mrr = new MarcRecordReader();
        $mr = new MarcReader( __DIR__ . '/data/umlaut.mrc' );
        $umlaut_record = $mr->nextRaw();
        $fields = $mrr->getDataFields( '100', $umlaut_record );
        $field = $mrr->getDataFieldAsAssocArray( $fields[0] );
        $this->assertEquals( $field['subfields'][0]['content'], 'Albrecher, Hansjörg.' );
    }

    public function testGetRecordAsAssocArray() {
        $mrr = new MarcRecordReader();
        $record = $mrr->getRecordAsAssocArray( $this->record );
        
        $this->assertEquals( count( $record ), 34 );

        // Check alias method
        $record2 = $mrr->get( $this->record );
        $this->assertEquals( $record, $record2 );
    }
}