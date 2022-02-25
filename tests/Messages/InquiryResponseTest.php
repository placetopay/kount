<?php

class InquiryResponseTest extends BaseTestCase
{
    public function testItParsesAnErrorResponse()
    {
        $result = $this->unserialize('czoxNToiTU9ERT1FCkVSUk89MjAxIjs=');
        $response = new \PlacetoPay\Kount\Messages\InquiryResponse($result);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(201, $response->errorCode());
        $this->assertEquals('MISSING_VERS', $response->errorKey());
        $this->assertEquals(2, count($response->data()));
        $this->assertNotNull(\PlacetoPay\Kount\Entities\KountError::errorMessage($response->errorKey()));
        $this->assertNull($response->fingerprint());
    }

    public function testItReturnsAnArrayWhenNeeded()
    {
        $this->assertArraySubset([
            201 => 'MISSING_VERS',
        ], \PlacetoPay\Kount\Entities\KountError::errorKey());

        $this->assertArraySubset([
            'MISSING_VERS' => 'Missing version of Kount, this is built into SDK but must be supplied by merchant if not using the SDK',
        ], \PlacetoPay\Kount\Entities\KountError::errorMessage());
    }

    public function testItParsesAnSiteErrorResponse()
    {
        $result = $this->unserialize('czo3MDE6Ik1PREU9RQpFUlJPPTMyMwpFUlJPUl8wPTMyMyBCQURfU0lURSBDYXVzZTogW1tURVNUXSBkb2VzIG5vdCBleGlzdCBmb3IgbWVyY2hhbnQgWzIwMTAwMF1dLCBGaWVsZDogW1NJVEVdLCBWYWx1ZTogW1RFU1RdCkVSUk9SXzE9MzYyIEJBRF9DQVJUIENhdXNlOiBbU2hvcHBpbmcgY2FydCB0eXBlIGluZGV4WzBdIGlzIG1pc3NpbmddLCBGaWVsZDogW1BST0RfVFlQRV0sIFZhbHVlOiBbMT0+MTExLCAyPT4yMzRdCkVSUk9SX0NPVU5UPTIKV0FSTklOR18wPTM5OSBCQURfT1BUTiBDYXVzZTogW3ZhbHVlIFsxMjNdIGRpZCBub3QgbWF0Y2ggcmVnZXggL14oW01OWF0/KT8kL10sIEZpZWxkOiBbQ1ZWUl0sIFZhbHVlOiBbMTIzXQpXQVJOSU5HXzE9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtrZXlfMT0+U29tZSBWYWx1ZSAxLCBrZXlfMj0+U29tZSBWYWx1ZSAyLCBrZXlfMz0+U29tZSBWYWx1ZSAzXQpXQVJOSU5HXzI9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtUaGUgbGFiZWwgW2tleV8xXSBpcyBub3QgZGVmaW5lZCBmb3IgbWVyY2hhbnQgSUQgWzIwMTAwMF0uIFRoZSBsYWJlbCBba2V5XzJdIGlzIG5vdCBkZWZpbmVkIGZvciBtZXJjaGFudCBJRCBbMjAxMDAwXS4gVGhlIGxhYmVsIFtrZXlfM10gaXMgbm90IGRlZmluZWQgZm9yIG1lcmNoYW50IElEIFsyMDEwMDBdLl0KV0FSTklOR19DT1VOVD0zIjs=');
        $response = new \PlacetoPay\Kount\Messages\InquiryResponse($result);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(323, $response->errorCode());
        $this->assertEquals('BAD_SITE', $response->errorKey());
        $this->assertEquals(2, $response->errorCount());
        $this->assertEquals('323 BAD_SITE Cause: [[TEST] does not exist for merchant [201000]], Field: [SITE], Value: [TEST]', $response->errors()[0]);
        $this->assertEquals('362 BAD_CART Cause: [Shopping cart type index[0] is missing], Field: [PROD_TYPE], Value: [1=>111, 2=>234]', $response->errors()[1]);
    }

    public function testItParsesASuccesfulInquiryWithDeclinedDecision()
    {
        $result = $this->unserialize('czoxMTQwOiJWRVJTPTA2MzAKTU9ERT1RClRSQU49UDA2WjA4UkMzS0wzCk1FUkM9MjAxMDAwClNFU1M9MQpPUkRSPTEKQVVUTz1EClNDT1I9MzgKR0VPWD1VUwpCUk5EPVZJU0EKUkVHTj0KTkVUVz1BCktBUFQ9TgpDQVJEUz0xCkRFVklDRVM9MQpFTUFJTFM9MQpWRUxPPTAKVk1BWD0wClNJVEU9REVGQVVMVApERVZJQ0VfTEFZRVJTPS4uLi4KRklOR0VSUFJJTlQ9ClRJTUVaT05FPQpMT0NBTFRJTUU9IApSRUdJT049CkNPVU5UUlk9ClBST1hZPQpKQVZBU0NSSVBUPQpGTEFTSD0KQ09PS0lFUz0KSFRUUF9DT1VOVFJZPQpMQU5HVUFHRT0KTU9CSUxFX0RFVklDRT0KTU9CSUxFX1RZUEU9Ck1PQklMRV9GT1JXQVJERVI9ClZPSUNFX0RFVklDRT0KUENfUkVNT1RFPQpSVUxFU19UUklHR0VSRUQ9NQpSVUxFX0lEXzA9NzI5ODMyClJVTEVfREVTQ1JJUFRJT05fMD1CaWxsaW5nIENvdW50cnkgaXMgbm90IEJJTiBDb3VudHJ5ClJVTEVfSURfMT03Mjk4NDgKUlVMRV9ERVNDUklQVElPTl8xPURldmljZSBEYXRhIENvbGxlY3RvciBNaXNzaW5nICYgU2NvcmUgPiAzNQpSVUxFX0lEXzI9NzI5ODUyClJVTEVfREVTQ1JJUFRJT05fMj1EZWNsaW5lIE5ldHdvcmsgQ2hhcmdlYmFja3MgPjEKUlVMRV9JRF8zPTcyOTg1NApSVUxFX0RFU0NSSVBUSU9OXzM9UmV2aWV3IE5vbi1Ob3JtYWwgTmV0d29yayBUeXBlClJVTEVfSURfND03Mjk4NzIKUlVMRV9ERVNDUklQVElPTl80PUNhcmQgb24gTmV0d29yayBDaGFyZ2ViYWNrIExpc3QgPjAKQ09VTlRFUlNfVFJJR0dFUkVEPTAKUkVBU09OX0NPREU9Ck1BU1RFUkNBUkQ9CkRERlM9CkRTUj0KVUFTPQpCUk9XU0VSPQpPUz0KUElQX0lQQUQ9ClBJUF9MQVQ9ClBJUF9MT049ClBJUF9DT1VOVFJZPQpQSVBfUkVHSU9OPQpQSVBfQ0lUWT0KUElQX09SRz0KSVBfSVBBRD0KSVBfTEFUPQpJUF9MT049CklQX0NPVU5UUlk9CklQX1JFR0lPTj0KSVBfQ0lUWT0KSVBfT1JHPQpXQVJOSU5HXzA9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtrZXlfMT0+U29tZSBWYWx1ZSAxXQpXQVJOSU5HXzE9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtUaGUgbGFiZWwgW2tleV8xXSBpcyBub3QgZGVmaW5lZCBmb3IgbWVyY2hhbnQgSUQgWzIwMTAwMF0uXQpXQVJOSU5HX0NPVU5UPTIiOw==');
        $response = new \PlacetoPay\Kount\Messages\InquiryResponse($result);

        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->errorCode());
        $this->assertNull($response->errorKey());
        $this->assertEquals('Card on Network Chargeback List >0', $response->rulesTriggered()[729872]);
    }

    public function testItParsesACorrectDataCollectedInquiryResponse()
    {
        $result = $this->unserialize('czoxMjgxOiJWRVJTPTA2MzAKTU9ERT1RClRSQU49UDAxSjBLWk4zMjlaCk1FUkM9MjAxMDAwClNFU1M9MwpPUkRSPTEyMzQKQVVUTz1EClNDT1I9MzMKR0VPWD1VUwpCUk5EPVZJU0EKUkVHTj0KTkVUVz1BCktBUFQ9WQpDQVJEUz0xCkRFVklDRVM9MQpFTUFJTFM9MQpWRUxPPTAKVk1BWD0wClNJVEU9REVGQVVMVApERVZJQ0VfTEFZRVJTPTgxQkJGNzc3MEMuLkQ5MjkwOUZGOTIuMTg2N0E5QjJDQi5ENjExMkMwOUY3CkZJTkdFUlBSSU5UPTRDMjQxMEJBMjJBNjRFMjFCRjBDNzNFQTg4RTQ4RDdFClRJTUVaT05FPTMwMApMT0NBTFRJTUU9MjAxNy0wNS0zMSAwMDoxOQpSRUdJT049Q09fMDIKQ09VTlRSWT1DTwpQUk9YWT1OCkpBVkFTQ1JJUFQ9WQpGTEFTSD1OCkNPT0tJRVM9WQpIVFRQX0NPVU5UUlk9VVMKTEFOR1VBR0U9RU4KTU9CSUxFX0RFVklDRT1OCk1PQklMRV9UWVBFPQpNT0JJTEVfRk9SV0FSREVSPU4KVk9JQ0VfREVWSUNFPU4KUENfUkVNT1RFPU4KUlVMRVNfVFJJR0dFUkVEPTUKUlVMRV9JRF8wPTcyOTgzMgpSVUxFX0RFU0NSSVBUSU9OXzA9QmlsbGluZyBDb3VudHJ5IGlzIG5vdCBCSU4gQ291bnRyeQpSVUxFX0lEXzE9NzI5ODUyClJVTEVfREVTQ1JJUFRJT05fMT1EZWNsaW5lIE5ldHdvcmsgQ2hhcmdlYmFja3MgPjEKUlVMRV9JRF8yPTcyOTg1NApSVUxFX0RFU0NSSVBUSU9OXzI9UmV2aWV3IE5vbi1Ob3JtYWwgTmV0d29yayBUeXBlClJVTEVfSURfMz03Mjk4NTgKUlVMRV9ERVNDUklQVElPTl8zPURldmljZSBMb2NhdGlvbiBMb3dlciBSaXNrIFJldmlldyBDb3VudHJpZXMKUlVMRV9JRF80PTcyOTg3MgpSVUxFX0RFU0NSSVBUSU9OXzQ9Q2FyZCBvbiBOZXR3b3JrIENoYXJnZWJhY2sgTGlzdCA+MApDT1VOVEVSU19UUklHR0VSRUQ9MApSRUFTT05fQ09ERT0KTUFTVEVSQ0FSRD0KRERGUz0yMDE3LTA1LTMwCkRTUj03Njh4MTM2NgpVQVM9TW96aWxsYS81LjAgKFgxMTsgTGludXggeDg2XzY0KSBBcHBsZVdlYktpdC81MzcuMzYgKEtIVE1MLCBsaWtlIEdlY2tvKSBDaHJvbWUvNTguMC4zMDI5LjExMCBTYWZhcmkvNTM3LjM2CkJST1dTRVI9Q2hyb21lIDU4LjAuMzAyOS4xMTAKT1M9TGludXgKUElQX0lQQUQ9ClBJUF9MQVQ9ClBJUF9MT049ClBJUF9DT1VOVFJZPQpQSVBfUkVHSU9OPQpQSVBfQ0lUWT0KUElQX09SRz0KSVBfSVBBRD0xODEuMTI4Ljg1LjIyMQpJUF9MQVQ9Ni4yNTE4CklQX0xPTj0tNzUuNTYzNgpJUF9DT1VOVFJZPUNPCklQX1JFR0lPTj1BbnRpb3F1aWEKSVBfQ0lUWT1NZWRlbGzDrW4KSVBfT1JHPVVORQpXQVJOSU5HX0NPVU5UPTAiOw==');
        $response = new \PlacetoPay\Kount\Messages\InquiryResponse($result);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('201000', $response->merchant());
        $this->assertEquals('3', $response->session());
        $this->assertEquals('1234', $response->order());
        $this->assertEquals('P01J0KZN329Z', $response->kountCode());
        $this->assertEquals(33, $response->score());
        $this->assertTrue($response->shouldDecline());
        $this->assertFalse($response->shouldApprove());
        $this->assertFalse($response->shouldReview());
        $this->assertEquals('4C2410BA22A64E21BF0C73EA88E48D7E', $response->fingerprint());
        $this->assertEquals('81BBF7770C..D92909FF92.1867A9B2CB.D6112C09F7', $response->deviceLayers());
        $this->assertEquals([
            '729832' => 'Billing Country is not BIN Country',
            '729852' => 'Decline Network Chargebacks >1',
            '729854' => 'Review Non-Normal Network Type',
            '729858' => 'Device Location Lower Risk Review Countries',
            '729872' => 'Card on Network Chargeback List >0',
        ], $response->rulesTriggered());
        $this->assertEquals('en', $response->language());
        $this->assertEquals('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36', $response->userAgent());
        $this->assertEquals('Linux', $response->operativeSystem());
        $this->assertEquals('768x1366', $response->screenResolution());
        $this->assertEquals('181.128.85.221', $response->ipAddress());
        $this->assertEquals('6.2518', $response->ipLatitude());
        $this->assertEquals('-75.5636', $response->ipLongitude());
        $this->assertEquals('CO', $response->ipCountry());
        $this->assertEquals('Antioquia', $response->ipState());
        $this->assertEquals('Medellín', $response->ipCity());
        $this->assertEquals('UNE', $response->ipProvider());
        $this->assertFalse($response->hasProxy());
        $this->assertTrue($response->hasJavascript());
    }
}
