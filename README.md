MarcReader
==========

This library is intended for use cases where speed is critical. 
It is *way* faster than [Pear/File_MARC](https://github.com/pear/File_MARC) 
especially when just some of the fields are needed. This makes the class 
well suited for indexing large record sets.

There is no writing functionality implemented. And there are almost no 
checks to see if the MARC record is valid or even just well-formed.

To sum it up: It is not a replacement for File_MARC, it is a library for 
special use cases.

Usage
-----

Most methods return raw data from the record. There are some methods
(getSomethingAsAssocArray) which return nested, associative arrays.

All methods of the MarcRecordReader class can be called statically.

```php
include_once __DIR__ . '/vendor/autoload.php';

use Umlts\MarcReader\MarcReader;
use Umlts\MarcReader\MarcRecordReader;

$mr = new MarcReader( '/path/to/marcfile.mrc', MarcReader::SOURCE_FILE );
$record = $mr->nextRaw();

// Get Leader
echo MarcRecordReader::getLeader( $record );

// Get Control Number (Tag 001)
echo MarcRecordReader::get001( $record );

// Get Control Fields
$control_fields = MarcRecordReader::getControlFields( '003', $record );

// Get Data Fields
$raw_data_fields = MarcRecordReader::getDataFields( '245', $record );
foreach ( $raw_data_fields as $field ) {
    print_r( MarcRecordReader::getDataFieldAsAssocArray( $field ) );
}

// Get whole record
$record_as_array = MarcRecordReader::getRecordAsAssocArray( $record );
print_r( $record_as_array );
```
