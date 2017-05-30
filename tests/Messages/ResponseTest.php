<?php


class ResponseTest extends BaseTestCase
{

    public function testItParsesAnErrorResponse()
    {
        $result = $this->unserialize("czoxNToiTU9ERT1FCkVSUk89MjAxIjs=");
        $response = new \PlacetoPay\Kount\Messages\Response($result);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(201, $response->errorCode());
        $this->assertEquals('MISSING_VERS', $response->errorKey());
        $this->assertEquals(2, sizeof($response->data()));
    }

    public function testItParsesAnSiteErrorResponse()
    {
        $result = $this->unserialize('czo3MDE6Ik1PREU9RQpFUlJPPTMyMwpFUlJPUl8wPTMyMyBCQURfU0lURSBDYXVzZTogW1tURVNUXSBkb2VzIG5vdCBleGlzdCBmb3IgbWVyY2hhbnQgWzIwMTAwMF1dLCBGaWVsZDogW1NJVEVdLCBWYWx1ZTogW1RFU1RdCkVSUk9SXzE9MzYyIEJBRF9DQVJUIENhdXNlOiBbU2hvcHBpbmcgY2FydCB0eXBlIGluZGV4WzBdIGlzIG1pc3NpbmddLCBGaWVsZDogW1BST0RfVFlQRV0sIFZhbHVlOiBbMT0+MTExLCAyPT4yMzRdCkVSUk9SX0NPVU5UPTIKV0FSTklOR18wPTM5OSBCQURfT1BUTiBDYXVzZTogW3ZhbHVlIFsxMjNdIGRpZCBub3QgbWF0Y2ggcmVnZXggL14oW01OWF0/KT8kL10sIEZpZWxkOiBbQ1ZWUl0sIFZhbHVlOiBbMTIzXQpXQVJOSU5HXzE9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtrZXlfMT0+U29tZSBWYWx1ZSAxLCBrZXlfMj0+U29tZSBWYWx1ZSAyLCBrZXlfMz0+U29tZSBWYWx1ZSAzXQpXQVJOSU5HXzI9Mzk5IEJBRF9PUFROIEZpZWxkOiBbVURGXSwgVmFsdWU6IFtUaGUgbGFiZWwgW2tleV8xXSBpcyBub3QgZGVmaW5lZCBmb3IgbWVyY2hhbnQgSUQgWzIwMTAwMF0uIFRoZSBsYWJlbCBba2V5XzJdIGlzIG5vdCBkZWZpbmVkIGZvciBtZXJjaGFudCBJRCBbMjAxMDAwXS4gVGhlIGxhYmVsIFtrZXlfM10gaXMgbm90IGRlZmluZWQgZm9yIG1lcmNoYW50IElEIFsyMDEwMDBdLl0KV0FSTklOR19DT1VOVD0zIjs=');
        $response = new \PlacetoPay\Kount\Messages\Response($result);
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

}