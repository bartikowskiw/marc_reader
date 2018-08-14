<?php
declare( strict_types = 1 );
namespace Umlts\MarcReader;

class MarcReader {

    public const SOURCE_FILE = 1;
    public const SOURCE_STRING = 2;

    public const RT = "\x1D";

    protected $source;
    protected $type;

    function __construct( string $source, int $type = self::SOURCE_FILE ) {
        
        $this->source = $source;
        $this->type = $type;
        
        switch ( $type ) {
            case self::SOURCE_FILE:
                // Check if the input file is valid
                $file_info = new \SplFileInfo( $source );
                if ( !$file_info->isReadable() ) {
                    throw new \RuntimeException( 'File "'. $source .'"not readable!' );
                }
                break;
            case self::SOURCE_STRING:
                // Do nothing
                break;
            default:
                throw new \InvalidArgumentException( 'Type does not exist!' );
        }

    }

    
}
