<?php
    /**
     * Copyright 2012, Chute Corporation
     * http://getchute.com
     *
     * Free to use under the MIT license.
     * http://www.opensource.org/licenses/mit-license.php
     */
require_once(dirname(__FILE__) . '/../lib/guzzle.phar');
use \Guzzle\Service\Client;


/**
 * Main class, client
 */
class Chute {
    public $options = array();
    public $client;
    public $chutes;
    public $uploads;
    public $assets;
    public $bundles;

	public function __construct($options = array()) {
		$this->set($options);
    }

	public function set($options = array()) {
		foreach ($options as $key => $value) { // overriding default options
			$this->options[$key] = $value;
        }
		$this->initializeResources(); // we need to reinitialize resources with the new options(may be new endpoint, token, etc)
    }

    private function initializeResources() {
        $this->chutes = new Chutes($this);
        $this->uploads = new Uploads($this);
        $this->assets = new Assets($this);
        $this->bundles = new Bundles($this);

        $this->client = new Client("http://api.getchute.com");
        $this->client->setDefaultHeaders(array(
            'x-client_id' => $this->options['id'],
            'Authorization' => "OAuth {$this->options['token']}"
        ));
    }

    public function search($options) { // general search method
        if (!$options['type'])
		    $options['type'] = 'all';

		$response = $this->client->get("v1/meta/{$options['type']}/{$options['key']}")->send();

        switch ($response->getStatusCode()) {
            case 201: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
		    default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }
}

class Bundles {
    private $chute;

    /** getting link to chute and ability to read options */
	function __construct($chute) {
        $this->chute = $chute;
    }

	/** creating bundle of existing assets */
	public function create($options) {
        $body = "asset_ids=".json_encode($options['ids']); //array('asset_ids' => $options['ids']);
		$response = $this->chute->client->post("v1/bundles", null, $body)->send();

		switch ($response->getStatusCode()) {
            case 201: return json_decode($response->getBody(true)); break;
			case 401: throw new Exception('invalid access token'); break;
			default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

	/** finding a bundle, options should be { id: 135235 } */
	public function find($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->get("v1/bundles/$id")->send();

        switch ($response->getStatusCode()) {
            case 200: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

	public function remove($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->delete("v1/bundles/$id")->send();

		switch ($response->getStatusCode()) {
            case 200: return true; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }
}

class Assets {
    private $chute;

    /** getting link to chute and ability to read options */
	function __construct($chute) {
        $this->chute = $chute;
    }

    /** finding assets, options should be { id: 125235|'asfsdgdfg', chuteId: 2352435|'hrdgfdh', comments: yes|no } */
	public function find($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->get("v1/assets/$id")->send();

        switch ($response->getStatusCode()) {
            case 200:
                $asset = json_decode($response->getBody(true))->data;
                if (!$options['comments'])
                    return $asset;

                $chuteId = $options['chuteId'] ? $options['chuteId'] : $options['chute'];
                $comments = $this->chute->client->get("v1/chutes/$chuteId/assets/$id/comments")->send();
                switch ($comments->getStatusCode()) {
                    case 200:
                        $asset->comments = json_decode($comments->getBody(true))->data;
                        return $asset; break;
                    case 401: throw new Exception('invalid access token'); break;
                    default: throw new Exception($comments->getBody(true)->error);
                }
                break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

    /** heart an asset, options should be { id: 125235|'shortcut' } */
	public function heart($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->get("v1/assets/$id/heart")->send();
        return ($response->getStatusCode() == 200);
    }

	/** unheart an asset, options should be { id: 125235|'shortcut' } */
	public function unheart($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->get("v1/assets/$id/unheart")->send();
        return ($response->getStatusCode() == 200);
    }

	/** searching for assets, options should be { key: 'id' } */
	public function search($options) {
		$this->client->search(array('type' => 'assets', 'key' => $options['key']));
    }

    /** removing asset, options should be { id: 12352345|'sdfgsdfgsdfg' } if removing one asset or { ids: [12345, 46543, 645345] } if removing multiple assets */
	public function remove($options) {
        $response = null;
		if ($options['id'])
            $id = $options['id'] ? $options['id'] : $options['shortcut'];
            $response = $this->chute->client->delete("v1/assets/$id")->send();

		if ($options['ids'])
			$response = $this->chute->client->post("v1/assets/remove", null, array('asset_ids' => json_encode($options['ids'])))->send();

        switch ($response->getStatusCode()) {
            case 200: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }
}

class Uploads {
    private $chute;

    /** getting link to chute and ability to read options */
	function __construct($chute) {
        $this->chute = $chute;
    }

	function request($options) {
        $body = array('files' => $options['files'], 'chutes' => $options['chutes']);
		$response = $this->chute->client->post("v2/uploads", null, json_encode($body))->send();

        if ($response->getStatusCode() != 200) throw new Exception($response->getStatusCode());
        return json_decode($response->getBody(true))->data;
    }

	function complete($uploadId) {
		$response = $this->chute->client->post("v2/uploads/$uploadId/complete")->send();

		if ($response->getStatusCode() != 200) throw new Exception($response->getStatusCode());
        return true;
    }

	/** generating token for an upload */
	public function upload($options) {
        $upload = $this->request($options);
		$assetIds = array(); // pushing asset ids
		$assetShortcuts = array(); // pushing asset shortcuts and returning all those at the end

		foreach ($upload->existing_assets as $asset) {
			$assetIds[] = $asset->id;
			$assetShortcuts[] = $asset->shortcut;
        }

		foreach ($upload->new_assets as $asset) {
            $assetIds[] = $asset->id;
            $assetShortcuts[] = $asset->shortcut;

            $file = file_get_contents($asset->upload_info->file_path);
            $this->chute->client->put($asset->upload_info->upload_url, array(
                'Authorization' => $asset->upload_info->signature,
                'Date' => $asset->upload_info->date,
                'Content-Type' => $asset->upload_info->content_type,
                'x-amz-acl' => 'public-read'
            ), $file)->send();
        }

		if ($this->complete($upload->id))
            return array('ids' => $assetIds, 'shortcuts' => $assetShortcuts);
    }
}

class Chutes {
    private $chute;

	/** getting link to chute and ability to get options */
	function __construct($chute) {
        $this->chute = $chute;
    }

	/** getting all chutes */
	public function all() {
		$response = $this->chute->client->get("v1/me/chutes")->send();

        switch ($response->getStatusCode()) {
            case 200: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

	/** adding assets to a specific chute, options should be { id: 1235235|'shortcut', ids: [], assets: [] } */
	public function addAssets($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $body = array('asset_ids' => ($options['ids'] ? $options['ids'] : $options['assets']));

		$response = $this->chute->client->post("v1/chutes/$id/assets/add", null, $body)->send();

        return ($response->getStatusCode() == 200);
    }

	/** removing assets to a specific chute, options should be { id: 1235235|'shortcut', ids: [], assets: [] } */
	public function removeAssets($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $body = array('asset_ids' => ($options['ids'] ? $options['ids'] : $options['assets']));

        $response = $this->chute->client->post("v1/chutes/$id/assets/remove", null, $body)->send();

        return ($response->getStatusCode() == 200);
    }

	/** finding only one chute, options should be { id: 1123123|'shortcut' } */
	public function find($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
		$response = $this->chute->client->get("v1/chutes/$id")->send();

        switch ($response->getStatusCode()) {
            case 200:
                $chute = json_decode($response->getBody(true))->data;
                if (!($options['contributors'] or $options['members'] or $options['parcels']))
                    return $chute;

                if ($options['contributors']) {
                    $contributors = $this->chute->client->get("v1/chutes/$id/contributors")->send();
                    $chute->contributors = ($contributors->getStatusCode() == 200) ? json_decode($contributors->getBody(true))->data : array();
                }

                if ($options['members']) {
                    $members = $this->chute->client->get("v1/chutes/$id/members")->send();
                    $chute->members = ($members->getStatusCode() == 200) ? json_decode($members->getBody(true))->data : array();
                }

                if ($options['parcels']) {
                    $parcels = $this->chute->client->get("v1/chutes/$id/parcels")->send();
                    $chute->parcels = ($parcels->getStatusCode() == 200) ? json_decode($parcels->getBody(true))->data : array();
                }

                return $chute;
                break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

	/** creating chute, options should be { name: 'Name of the Chute' } */
	public function create($options) {
		$data = array();
		foreach ($options as $key => $value) {
            $data["chute[$key]"] = $value;
        }

		$response = $this->chute->client->post("v1/chutes", null, $data)->send();

        switch ($response->getStatusCode()) {
            case 201: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

	/** updating chute, options should be { name: 'New name for the Chute', id: 1243234|'shortcut' } */
	public function update($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $data = array();
        foreach ($options as $key => $value) {
            if ($key != 'id' and $key != 'shortcut')
                $data["chute[$key]"] = $value;
        }

        $response = $this->chute->client->put("v1/chutes/$id")->setBody("chute[name]={$options['name']}");
        var_dump($response);
        $response = $response->send();

        switch ($response->getStatusCode()) {
            case 200: return json_decode($response->getBody(true))->data; break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }

    /** searching for chutes, options should be { key: 'id' } */
	public function search($options) {
		return $this->chute->search(array('type' => 'chutes', 'key' => $options['key']));
    }

	/** removing chute, options should be { id: 123235|'shortcut' } */
	public function remove($options) {
        $id = $options['id'] ? $options['id'] : $options['shortcut'];
        $response = $this->chute->client->delete("v1/chutes/$id")->send();

        switch ($response->getStatusCode()) {
            case 200: return array(); break;
            case 401: throw new Exception('invalid access token'); break;
            default: throw new Exception(json_encode(json_decode($response->getBody(true))->error));
        }
    }
}
