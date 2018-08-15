<?php
declare( strict_types = 1 );
namespace Umlts\MarcReader;

/**
 * Class MarcReader
 */
class MarcReader {

    public const SOURCE_FILE = 1;
    public const SOURCE_STRING = 2;

    public const RT = "\x1D";
    public const MAX_RECORD_LENGTH = 99999;

    protected $source;
    protected $pos;
    protected $type;

    /**
     * Constructor
     *
     * @param string $source
     * @param integer $type
     */
    function __construct( string $source, int $type = self::SOURCE_FILE ) {
        
        $this->source = $source;
        $this->type = $type;
        
        switch ( $type ) {

            case self::SOURCE_FILE:
                // Check if the input file is valid
                $file_info = new \SplFileInfo( $source );
                if ( !$file_info->isReadable() ) {
                    throw new \RuntimeException( 'File "'. $source .'" not readable!' );
                }
                if ( false === $this->source = fopen( $source, 'rb' ) ) {
                    // This is a fallback. Ignore.
                    // @codeCoverageIgnoreStart
                    throw new \RuntimeException( 'File "'. $source .'" is invalid!' );
                    // @codeCoverageIgnoreEnd
                }
                break;

            case self::SOURCE_STRING:
                $this->pos = 0;
                break;

            default:
                throw new \InvalidArgumentException( 'Type does not exist!' );
        }

    }

    /**
     * Get next raw record
     * 
     * This function mimics the behavoir of the nextRaw method from the 
     * File_MARC library.
     *
     * @return string|bool
     *   Returns the next raw record or false if no records are left
     */
    public function nextRaw() {

        switch ( $this->type ) {

            case self::SOURCE_FILE:
                $record = stream_get_line(
                    $this->source,
                    self::MAX_RECORD_LENGTH,
                    self::RT
                );
                $record .= self::RT;
                break;

            case self::SOURCE_STRING:
                $length = strpos( $this->source, self::RT );
                if ( $length === false ) { return false; }
                $record = substr( $this->source, $this->pos, $length + 1 );
                $this->source = substr( $this->source, $length + 1 );
                $this->pos += $length;
                break;

            default:
                // This is a fallback. Ignore.
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException( 'Type does not exist!' );
                // @codeCoverageIgnoreEnd
        }

        if ( empty( $record ) ) { return false; }

        return $record;
    }

    
}
