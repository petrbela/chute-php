# Chute

Chute makes it possible for you to easily organize, store and serve photos and videos.  This PHP library provides you with a wrapper for the Chute REST API.
You can learn more about Chute at [http://getchute.com](http://getchute.com) and explore the API at [http://picture.io](http://picture.io).

# Installation

Take `src/chute.php` and `lib/guzzle.phar` and put them into your project.

# Getting Started

1. Sign up for an account at [Chute](http://auth.getchute.com/signup?authorization=4f541b8e38ecef3f4d000001)
2. Install this library
3. Read the [API docs](http://explore.picture.io) for better understanding

# Usage

Initialize:

```php
require('src/chute.php');
$client = new Chute();
$client->set(array(
  'token' => 'access token',
  'id' => 'app id'
));
```

## Chutes

Chutes allow you to manage sets of photos - you can think of them as albums.

Find:

```php
// find all chutes
$chutes = $client->chutes->all();
foreach ($chutes as $chute) {
  echo $chute->id;
}

// find chute with ID=12345
$chute = $client->chutes->find(array('id' => 12345);
echo $chute->id;

// find chute with ID=12345 with contributors list inside
$chute = $client->chutes->find(array('id' => 12345, 'contributors' => true));
echo $chute->contributors;

// find chute with ID=12345 with members list inside
$chute = $client->chutes->find(array('id' => 12345, 'members' => true));
echo $chute->members;

// find chute with ID=12345 with parcels list inside
$chute = $client->chutes->find(array('id' => 12345, 'parcels' => true));
echo $chute->parcels;

// find chute with ID=12345 with everything inside
$chute = $client->chutes->find(array(
  'id' => 12345,
  'contributors' => true,
  'members' => true,
  'parcels' => true
));
echo $chute->id . $chute->contributors . $chute->members . $chute->parcels;
```

Create:

```php
// create a chute
$chute = $client->chutes->create(array('name' => 'Chute name'));
```

Update:

```php
// change name of chute with ID=235345 to 'New name'
$chute = $client->chutes->update(array('id' => '235345', 'name' => 'New name'));
```

Remove:

```php
// remove chute with ID=12345
$client->chutes->remove(array('id' => 12345));
```

## Assets

Assets represent photos and videos contained within a Chute.

Find:

```php
// find asset with ID=12345
$asset = $client->assets->find(array('id' => 12345));
echo $asset->id;

// find asset with ID=12345 with comments inside
$asset = $client->assets->find(array('id' => 12345, 'comments' => true));
echo $asset->comments;
```

Customize:

```php
$asset = $client->assets->find(array('id' => 12345));
$asset->url // http://media.getchute.com/media/$id
$asset->url."/w/640" // http://media.getchute.com/media/$id/w/640
$asset->url."/h/480" // http://media.getchute.com/media/$id/h/480
$asset->url."/640x480" // http://media.getchute.com/media/$id/640x480
$asset->url."/fit/640x480" // http://media.getchute.com/media/$id/fit/640x480
```

Like:

```php
// +1 to asset with ID=12345
$client->assets->heart(array('id' => 12345));

// -1 to asset with ID=12345
$client->assets->unheart(array('id' => 12345));
```

Remove:

```php
// remove asset with ID=12345
$client->assets->remove(array('id' => 12345));
```

## Bundles

Bundles allow you to create dynamic sets of photos.

Find:

```php
// find bundle with ID=12345
$bundle = $client->bundles->find(array('id' => 12345));
```

Create:

```php
// create bundle with assets 134234 and 534125
$bundle = $client->bundles->create(array(
  'ids' => array(134234, 534125)
));
```

Remove:

```php
// remove bundle with ID=12345
$client->bundles->remove(array('id' => 12345));
```

## Uploads

Chute provides a simple upload flow that provides image processing and more.

```php
// info about files
$files = array(
  array('filename' => 'image.jpg', 'size' => filesize('image.jpg'), 'md5' => md5_file('image.jpg'))
  // , second file etc.
);
// ID(s) of chute(s) to which you want to upload the image(s)
$chutes = array(12423523);

$assets = $client->uploads->upload(array('files' => $files, 'chutes' => $chutes));
// $assets is an array of asset IDs/shortcuts which were just uploaded
// $assets = array('ids' => array([asset1_id, asset2_id]), 'shortcuts' => array([asset1_shortcut, asset2_shortcut]))
```

# Tests

Put your app credentials (access token and id) into `test/ChuteTest.php` and run it with `php test/ChuteTest.php`.
