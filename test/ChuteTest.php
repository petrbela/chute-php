<?php

require_once('simpletest/autorun.php');

require_once('../src/chute.php');

class ChuteTest extends UnitTestCase {

    private $client;
    private $chuteId;
    private $testImage;

    function ChuteTest() {
        ////// fill in this information before running the test
        $this->client = new Chute(array(
            // fill in your auth credentials
            'token' => '2e37243432110c4155283df0685a4f08f80c157815a858cdeedc6f9cd19c7496',
            'id' => '5050dc193f59d821a600004c'
        ));
        $this->testImage = '/Users/petrbela/Desktop/stevejobs.png'; // put some test image here
        ////////////////
    }

    //
    //

    //it 'should create chute', (done) ->
    function testCreateChute() {
        $chute = $this->client->chutes->create(array('name' => 'Testing things'));
        $this->assertEqual('Testing things', $chute->name);
        $this->assertNotNull($chute->id);
        $this->chuteId = $chute->id;
        //client.chutes.create name: 'Testing things', (err, chute) ->
        //chuteId = chute.id
        //err.should.equal(no) and chute.name.should.equal('Testing things')
        //do done
    }

    //it 'should get all chutes', (done) ->
    function testGetAllChutes() {
        $chutes = $this->client->chutes->all();
        $this->assertTrue(sizeof($chutes) > 0);
        //client.chutes.all (err, chutes) ->
        //chutes.length.should.be.above 0
        //do done
    }

    //it 'should get a chute', (done) ->
    function testFindChute() {
        $chute = $this->client->chutes->find(array('id' => $this->chuteId));
        $this->assertNotNull($chute);
        //client.chutes.find id: chuteId, (err, chute) ->
        //err.should.equal no
        //do done
    }

    //it 'should update chute', (done) ->
    function testUpdateChute() {
        $chute = $this->client->chutes->update(array('id' => $this->chuteId, 'name' => 'Wohoo'));
        $this->assertEqual('Wohoo', $chute->name);
        //client.chutes.update id: chuteId, name: 'Wohoo', (err, chute) ->
        //err.should.equal(no) and chute.name.should.equal('Wohoo')
        //do done
    }

    //it 'should find chute\'s contributors', (done) ->
    function testFindChuteContributors() {
        $chute = $this->client->chutes->find(array('id' => $this->chuteId, 'contributors' => true));
        $this->assertNotNull($chute);
        $this->assertEqual(0, sizeof($chute->contributors));
        //client.chutes.find id: chuteId, contributors: yes, (err, chute) ->
        //err.should.equal(no) and chute.contributors.length.should.equal(0)
        //do done
    }

    //it 'should find chute\'s members', (done) ->
    function testFindChuteMembers() {
        $chute = $this->client->chutes->find(array('id' => $this->chuteId, 'members' => true));
        $this->assertNotNull($chute);
        $this->assertEqual(1, sizeof($chute->members));
        //client.chutes.find id: chuteId, members: yes, (err, chute) ->
        //err.should.equal(no) and chute.members.length.should.equal(1)
        //do done
    }

    //it 'should find chute\'s parcels', (done) ->
    function testFindChuteParcels() {
        $chute = $this->client->chutes->find(array('id' => $this->chuteId, 'parcels' => true));
        $this->assertNotNull($chute);
        $this->assertEqual(0, sizeof($chute->parcels));
        //client.chutes.find id: chuteId, parcels: yes, (err, chute) ->
        //err.should.equal(no) and chute.parcels.length.should.equal(0)
        //do done
    }

    //it 'should remove chute', (done) ->
    function testRemoveChute() {
        $result = $this->client->chutes->remove(array('id' => $this->chuteId));
        $this->assertNotNull($result);
        //client.chutes.remove id: chuteId, (err) ->
        //err.should.equal no
        //do done
    }

    //describe 'Uploads', ->
    //
    //it 'should upload file', (done) ->
    function testUploadFile() {
        $chute = $this->client->chutes->create(array('name' => 'Beach'));
        //before (done) ->
        //client.chutes.create name: 'Beach', (err, chute) ->
        //chuteId = chute.id
        //do done

        $assets = $this->client->uploads->upload(array('chutes' => array($chute->id), 'files' => array(array(
            'filename' => $this->testImage,
            'size' => filesize($this->testImage),
            'md5' => filesize($this->testImage)
        ))));
        $this->assertTrue(sizeof($assets) > 0);
        //client.uploads.upload files: [{ filename: testImage, size: fs.statSync(testImage).size, md5: require('crypto').createHash('md5').update(fs.readFileSync(testImage, 'utf-8')).digest('hex') }], chutes: [chuteId], (err, assets) ->
        //assetId = assets.ids[0]
        //do done
    }

    //describe 'Bundles', ->
    //it 'should create a bundle', (done) ->
    //client.bundles.create ids: [assetId], (err, bundle) ->
    //bundleId = bundle.id
    //err.should.equal(no) and bundle.id.should.be.above(0)
    //do done
    //
    //it 'should find a bundle', (done) ->
    //client.bundles.find id: bundleId, (err, bundle) ->
    //err.should.equal(no)
    //do done
    //
    //it 'should remove bundle', (done) ->
    //client.bundles.remove id: bundleId, (err) ->
    //err.should.equal(no)
    //do done
    //
    //describe 'Assets', ->
    //it 'should find an asset', (done) ->
    //client.assets.find id: assetId, (err, asset) ->
    //err.should.equal(no) and asset.id.should.equal(assetId)
    //do done
    //
    //it 'should find an asset with comments inside', (done) ->
    //client.assets.find chuteId: chuteId, id: assetId, comments: yes, (err, asset) ->
    //err.should.equal(no) and asset.comments.length.should.equal(0)
    //do done
    //
    //it 'should heart an asset', (done) ->
    //client.assets.heart id: assetId, (err) ->
    //err.should.equal(no)
    //do done
    //
    //it 'should unheart an asset', (done) ->
    //client.assets.unheart id: assetId, (err) ->
    //err.should.equal(no)
    //do done
    //
    //it 'should remove asset', (done) ->
    //client.assets.remove id: assetId, (err) ->
    //err.should.equal(no)
    //do done

}