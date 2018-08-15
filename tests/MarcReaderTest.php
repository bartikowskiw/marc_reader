<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcReader\MarcReader;

/**
 * @covers Umlts\MarcReader\MarcReader
 */
final class MarcReaderTest extends TestCase {

    protected $umlaut_record_b64 = 'MDE4MjduMm0gYTIyMDAzOTc4aSA0NTAwMDAxMDAyMjAwMDAwMDAzMDAwNzAwMDIyMDA1MDAxNzAwMDI5MDA2MDA0NTAwMDQ2MDA3MDA0NTAwMDkxMDA4MDA0MTAwMTM2MDEwMDAxNTAwMTc3MDIwMDAyNjAwMTkyMDM1MDAyNjAwMjE4MDQwMDAzMTAwMjQ0MDQyMDAwODAwMjc1MDUwMDAyMjAwMjgzMDgyMDAxODAwMzA1MTAwMDAyNjAwMzIzMjQ1MDI4ODAwMzQ5MjYwMDA0NjAwNjM3MzAwMDAxNDAwNjgzNDkwMDAyNzAwNjk3NTA0MDA1MTAwNzI0NjUwMDAyMzAwNzc1NjUwMDAxNzAwNzk4NzAwMDAxOTAwODE1NzAwMDAyMDAwODM0ODU2MDE5OTAwODU0ODU2MDE0NjAxMDUzODU2MDE4MTAxMTk5OTA3MDAxNjAxMzgwOTQ1MDAxMDAxMzk2OTQ1MDAwNzAxNDA2OTQ1MDAxMDAxNDEzOTk4MDAwNjAxNDIzHk9DTTFib29rc3NqMDAwMTg1NjI0MR5XYVNlU1MeMjAxODA1MTQwOTE1MDUuMB5tICAgICAgICBkICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIB5jciAgbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIB4xNzA3MDdzMjAxNyAgICBuanUgICAgIG9iICAgIDAwMSAwIGVuZyBkHiAgH2EyMDE3MDE3MTg0HiAgH2E5NzgwNDcwNzcyNjgzIChjbG90aCkeICAfYShXYVNlU1Mpc3NqMDAwMTg1NjI0MR4gIB9hRExDH2JlbmcfY0RMQx9kRExDH2RXYVNlU1MeICAfYXBjYx4wMB9hSEc4MDgzH2IuQTQzIDIwMTceMDAfYTM2OC8uMDEyMh8yMjMeMSAfYUFsYnJlY2hlciwgSGFuc2rDtnJnLh4xMB9hUmVpbnN1cmFuY2UfaFtlbGVjdHJvbmljIHJlc291cmNlXSA6H2JhY3R1YXJpYWwgYW5kIHN0YXRpc3RpY2FsIGFzcGVjdHMgLx9jYnkgSGFuc2rDtnJnIEFsYnJlY2hlciwgVW5pdmVyc2l0eSBvZiBMYXVzYW5uZSwgU3dpdHplcmxhbmQ7IEphbiBCZWlybGFudCwgS2F0aG9saWVrZSBVbml2ZXJzaXRlaXQgTGV1dmVuLCBCRSwgVW5pdmVyc2l0eSBvZiB0aGUgRnJlZSBTdGF0ZSwgU291dGggQWZyaWNhOyBKb3plZiBMLiBUZXVnZWxzLCBLYXRob2xpZWtlIFVuaXZlcnNpdGVpdCBMZXV2ZW4sIEJFLh4gIB9hSG9ib2tlbiwgTkogOh9iSm9obiBXaWxleSAmIFNvbnMsH2NbMjAxN10eICAfYXBhZ2VzIGNtLh4wIB9hU3RhdGlzdGljcyBpbiBwcmFjdGljZR4gIB9hSW5jbHVkZXMgYmlibGlvZ3JhcGhpY2FsIHJlZmVyZW5jZXMgYW5kIGluZGV4Lh4gMB9hQWN0dWFyaWFsIHNjaWVuY2UuHiAwH2FSZWluc3VyYW5jZS4eMSAfYUJlaXJsYW50LCBKYW4uHjEgH2FUZXVnZWxzLCBKZWYgTC4eNDAfek1VIG9ubGluZSBhY2Nlc3MgdmlhIFNhZmFyaSBUZWNobmljYWwgQm9va3MgKGFjY2VzcyB0byBTYWZhcmkgYm9va3MgbGltaXRlZCB0byA1IHNpbXVsdGFuZW91cyB1c2VycykfdWh0dHA6Ly9wcm94eS5tdWwubWlzc291cmkuZWR1L2xvZ2luP3VybD1odHRwczovL3Byb3F1ZXN0LnNhZmFyaWJvb2tzb25saW5lLmNvbS85NzgwNDcwNzcyNjgzHjQ1H3pNaXNzb3VyaSBTJlQgT25saW5lIEFjY2VzcyB2aWEgU2FmYXJpIFRlY2huaWNhbCBCb29rcx91aHR0cDovL2xpYnByb3h5Lm1zdC5lZHUvbG9naW4/dXJsPWh0dHBzOi8vcHJvcXVlc3Quc2FmYXJpYm9va3NvbmxpbmUuY29tLzk3ODA0NzA3NzI2ODMeNDQfelVNS0MgT25saW5lIEFjY2VzcyB2aWEgU2FmYXJpIFRlY2huaWNhbCBCb29rcx91aHR0cDovL3Byb3h5LmxpYnJhcnkudW1rYy5lZHUvbG9naW4/dXJsPWh0dHBzOi8vcHJvcXVlc3Quc2FmYXJpYm9va3NvbmxpbmUuY29tLzk3ODA0NzA3NzI2ODMfM0xpbWl0ZWQgdG8gZm91ciBzaW11bHRhbmVvdXMgdXNlcnMuHiAgH2EuYjEyMzA5OTg1Nx4gIB9scndlaWkeICAfbGtlHiAgH2xjZWVpaR4gIB9kMh4d';

    public function testCanBeCreated() {
        $mr = new MarcReader( __DIR__ . '/data/cattle.mrc' );
        $this->assertInstanceOf( MarcReader::class, $mr );
    }

    public function testConstructInvalidTypeException() {

        // Invalid type
        $this->expectException( \InvalidArgumentException::class );
        $mr = new MarcReader( __DIR__ . '/data/cattle.mrc', 123 );

    }

    public function testConstructInvalidFileException() {
            // Invalid file
            $this->expectException( \RuntimeException::class );
            $mr = new MarcReader( __DIR__ . '/data/cttle.mrc' );
    }

    public function testNextRaw() {

        $mr = new MarcReader( __DIR__ . '/data/cattle.mrc' );
        $raw_record = $mr->nextRaw();

        $this->assertEquals( strlen( $raw_record ), 1732 );
        $this->assertEquals( substr( $raw_record, -1 ), MarcReader::RT );

        $string_records = base64_decode( $this->umlaut_record_b64 );
        $string_records .= $string_records;

        $mr2 = new MarcReader(
            $string_records,
            MarcReader::SOURCE_STRING
        );

        while ( $raw_record2 = $mr2->nextRaw() ) {
            $this->assertEquals( substr( $raw_record2, -1 ), MarcReader::RT );
            $this->assertEquals(
                strpos( $raw_record2, '01827n2m a22003978i 4500' ),
                0
            );
        }
    }

}