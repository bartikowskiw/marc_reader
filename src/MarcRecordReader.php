<?php
declare( strict_types = 1 );
namespace Umlts\MarcReader;

/**
 * Class parsing raw MARC data
 * 
 * More information on the file format:
 * https://www.loc.gov/marc/specifications/specrecstruc.html
 * 
 */
class MarcRecordReader {

    public const FT = "\x1E";
    public const RT = "\x1D";
    public const DELIMITER = "\x1F";

    public const LEADER_LENGTH = 24;
    public const LEADER_RECORD_LENGTH = 0;
    public const LEADER_RECORD_LENGTH_SIZE = 5;
    public const LEADER_RECORD_STATUS = 5;
    public const LEADER_RECORD_STATUS_SIZE = 1;
    public const LEADER_TYPE_OF_RECORD = 6;
    public const LEADER_TYPE_OF_RECORD_SIZE = 1;
    public const LEADER_CHARACTER_CODING_SCHEME = 9;
    public const LEADER_CHARACTER_CODING_SCHEME_SIZE = 1;
    public const LEADER_INDICATOR_COUNT = 10;
    public const LEADER_INDICATOR_COUNT_SIZE = 1;
    public const LEADER_SUBFIELD_CODE_LENGTH = 11;
    public const LEADER_SUBFIELD_CODE_LENGTH_SIZE = 1;
    public const LEADER_BASE_ADDRESS_OF_DATA = 12;
    public const LEADER_BASE_ADDRESS_OF_DATA_SIZE = 5;
    public const LEADER_ENTRY_MAP = 20;
    public const LEADER_ENTRY_MAP_SIZE = 4;

    public const DIR_ENTRY_LENGTH = 12;
    public const DIR_TAG = 0;
    public const DIR_TAG_SIZE = 3;
    public const DIR_LENGTH_OF_FIELD = 3;
    public const DIR_LENGTH_OF_FIELD_SIZE = 4;
    public const DIR_STARTING_CHARACTER_POSITION = 7;
    public const DIR_STARTING_CHARACTER_POSITION_SIZE = 5;

    /**
     * @var string
     */
    protected $data;

    /**
     * Get the base address of the MARC data
     *
     * @return void
     */
    public static function getBaseAddress( string $record ) : int {
        return intval( substr(
            $record,
            self::LEADER_BASE_ADDRESS_OF_DATA,
            self::LEADER_BASE_ADDRESS_OF_DATA_SIZE
        ) );
    }

    /**
     * Get raw directory
     *
     * @param string $record
     * @return string
     */
    public static function getDirectoryRaw( string $record ) : string {
        return substr(
            $record,
            self::LEADER_LENGTH,
            self::getBaseAddress( $record ) - self::LEADER_LENGTH - 1
        );
    }

    /**
     * Get field tag from raw directory entry
     *
     * @param string $entry
     * @return string
     */
    public static function getDirTag( string $entry ) : string {
        return substr(
            $entry,
            self::DIR_TAG,
            self::DIR_TAG_SIZE
        );
    }

    /**
     * Get length of field from raw direcotry entry
     *
     * @param string $entry
     * @return integer
     */
    public static function getDirLength( string $entry ) : int {
        return intval(
            substr(
                $entry,
                self::DIR_LENGTH_OF_FIELD,
                self::DIR_LENGTH_OF_FIELD_SIZE
            )
        );
    }

    /**
     * Get offset of field data relative to base address from 
     * raw directory entry
     *
     * @param string $entry
     * @return integer
     */
    public static function getDirPosition( string $entry ) : int {
        return intval(
            substr(
                $entry,
                self::DIR_STARTING_CHARACTER_POSITION,
                self::DIR_STARTING_CHARACTER_POSITION_SIZE
            )
        );
    }

    /**
     * Get associative array for raw directory entry
     *
     * @param string $entry
     * @return array
     */
    public static function getEntryAsAssocArray( string $entry ) : array {
        return [
            'tag' => self::getDirTag( $entry ),
            'length' => self::getDirLength( $entry ),
            'position' => self::getDirPosition( $entry ),
        ];
    }

    /**
     * Undocumented function
     *
     * @param string $record
     * @return array
     */
    public static function getDirectory( string $record ) : array {
        $dir = self::getDirectoryRaw( $record );
        return str_split( $dir, self::DIR_ENTRY_LENGTH );
    }

    /**
     * Get 001 MARC Field
     * 
     * This function is optimized to be as fast as possible. It is
     * also used in the getControlField method, so the speed advantage
     * is just about 10 to 20 percent.
     *
     * @param string $record
     * @return string
     */
    public static function get001( string $record ) : string {

        $entry = substr( $record, self::LEADER_LENGTH, self::DIR_ENTRY_LENGTH );
        
        if ( strpos( $entry, '001') !== 0 ) {
            throw new \RuntimeException( 'Field not found.');
        }

        $base_address = intval( substr(
            $record,
            self::LEADER_BASE_ADDRESS_OF_DATA,
            self::LEADER_BASE_ADDRESS_OF_DATA_SIZE
        ) );

        $length = intval( substr(
            $entry,
            self::DIR_LENGTH_OF_FIELD,
            self::DIR_LENGTH_OF_FIELD_SIZE
        ) );

        return substr( $record, $base_address, --$length );
    }

    /**
     * Get the raw contents of a field
     *
     * @param string $entry
     * @param string $record
     * @param integer $base_address
     * @return string
     */
    public static function getRawField( string $entry, string $record, int $base_address = -1 ) : string {
        
        if ( $base_address <= 0 ) {
            $base_address = self::getBaseAddress( $record );
        }

        return substr(
            $record,
            $base_address + self::getDirPosition( $entry ),
            self::getDirLength( $entry ) - 1
        );
    }

    /**
     * Get a Control Field
     *
     * @param string $tag
     * @param string $record
     * @return string
     */
    public static function getControlField( string $tag, string $record ) : string {

        // Check tag
        if ( !preg_match( '/^00[0-9]$/', $tag ) ) {
            throw new \InvalidArgumentException( 'Tag "' . $tag . '" not a valid Control Field Tag.' );
        }

        // Use optimized function for MARC Tag 001
        if ( $tag == '001' ) { return self::get001( $record ); }

        $dir = self::getDirectory( $record );
        
        foreach ( $dir as $entry ) {
            if ( self::getDirTag( $entry ) === $tag ) {
                return self::getRawField( $entry, $record );
            }
        }

        // Nothing found
        throw new \RuntimeException( 'Field not found.');
    }

    /**
     * Get raw contents of data fields
     *
     * @param string $tag
     * @param string $record
     * @return string[]
     */
    public static function getDataFields( string $tag, string $record ) : array {

        // Check tag
        if ( !preg_match( '/^[0-9]{3}$/', $tag ) && intval( $tag ) < 10 ) {
            throw new \InvalidArgumentException( 'Tag "' . $tag . '" not a valid Control Field Tag.' );
        }

        $fields = [];
        $dir = self::getDirectory( $record );
        $base_address = self::getBaseAddress( $record );

        foreach ( $dir as $entry ) {
            if ( self::getDirTag( $entry ) === $tag ) {
                $fields[] = self::getRawField( $entry, $record, $base_address );
            }
        }

        return $fields;
    }

    /**
     * Get data field(s) as associative array
     *
     * @param string $tag
     * @param string $record
     * @return array
     */
    public static function getDataFieldAsAssocArray( string $field ) : array {

        return [
            'ind1' => self::getFirstIndicator( $field ),
            'ind2' => self::getFirstIndicator( $field ),
            'subfields' => self::getSubfieldsAsAssocArray(
                self::getSubfields( $field )
            ),
        ];

    }

    /**
     * Get first indicator from raw field data
     *
     * @param string $field
     * @return string
     */
    public static function getFirstIndicator( string $field ) : string {
        return substr( $field, 0, 1 );
    }

    /**
     * Get first indicator from raw field data
     *
     * @param string $field
     * @return string
     */
    public static function getSecondIndicator( string $field ) : string {
        return substr( $field, 1, 1 );
    }

    /**
     * Get raw subfields from raw field data
     *
     * @param string $field
     * @return void
     */
    public static function getSubfields( string $field ) {
        return substr( $field, 3 );
    }

    /**
     * Get subfields as associative array from raw subfield data
     *
     * @param string $subfields
     * @return array
     */
    public static function getSubfieldsAsAssocArray( string $subfields ) : array {
        $subfields_array = [];
        $elements = explode( self::DELIMITER, $subfields );

        foreach ( $elements as $element ) {
            $subfields_array[] = [
                'tag' => substr( $element, 0, 1 ),
                'content' => substr( $element, 1 ),
            ];
        }
        return $subfields_array;
    }

    /**
     * Get the whole record as an associative array
     *
     * @param string $record
     * @return void
     */
    public static function getRecordAsAssocArray( string $record ) {

        $record_array = [];
        $dir = self::getDirectory( $record );
        $base_address = self::getBaseAddress( $record );

        foreach ( $dir as $entry ) {
            $tag = self::getDirTag( $entry );

            if ( preg_match( '/00[0-9]/', $tag ) ) {
                $temp = [ 'content' => self::getRawField( $entry, $record, $base_address ) ];
            } else {
                $temp = self::getDataFieldAsAssocArray(
                    self::getRawField( $entry, $record, $base_address )
                );
            }

            $record_array[] = array_merge(
                [ 'tag' => $tag ],
                $temp
            );
            
        }

        return $record_array;
    }

    /**
     * Alias for getRecordAsAssocArray
     *
     * @param string $record
     * @return void
     */
    public static function get( string $record ) {
        return self::getRecordAsAssocArray( $record );
    }

}