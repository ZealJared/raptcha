<?php declare(strict_types=1);

class Challenge
{
  public static function getMaximumPassingError(): int
  {
    return 2;
  }

  public static function getMinimumRotation(): int
  {
    return 0; // may or may not affect bots, depending on bot code
  }

  public static function saveImage(): object
  {
    $jsonData = file_get_contents('php://input');
    if (empty($jsonData)) {
      $jsonData = '';
    }
    /** @var mixed $imageObject */
    $imageObject = json_decode($jsonData);
    /** @var string|false $image */
    $image = is_object($imageObject) && property_exists($imageObject, 'imageDataUrl') ? $imageObject->imageDataUrl : false;
    if (is_string($image)) {
      [$meta, $data64] = explode(',', $image);
      $data = base64_decode($data64, true);
      $imagick = new Imagick();
      $imagick->readImageBlob($data);
      $imagick->setFormat('PNG');
      $imagick->writeImage(sprintf('../img/%s.png', time()));
    }

    return (object) ['message' => 'Saved'];
  }

  public static function get(): object
  {
    $files = glob('../img/*.png');
    if (empty($files)) {
      throw new Exception('No images found.');
    }
    $file = $files[rand(0, count($files) - 1)];
    $imagick = new Imagick($file);
    $rotateDegrees = rand(0 + self::getMinimumRotation(), 360 - (self::getMinimumRotation() - 1));
    $imagick->rotateImage('#00000000', $rotateDegrees);
    $newWidth = $imagick->getImageWidth();
    $newHeight = $imagick->getImageHeight();
    $imagick->setImagePage($newWidth, $newHeight, 0, 0);
    $imagick->cropImage(300, 300, intval(($newWidth - 300) / 2), intval(($newHeight - 300) / 2));
    $imageData = $imagick->getImageBlob();
    $imageData64 = base64_encode($imageData);
    $src = sprintf('data: %s;base64,%s', mime_content_type($file), $imageData64);
    $challengeId = bin2hex(random_bytes(16));

    $db = DB::getConnection();
    $statement = $db->prepare('INSERT INTO challenge(id, rotation) VALUES(:id, :rotation);');
    $statement->execute(['id' => $challengeId, 'rotation' => $rotateDegrees]);

    return (object) [
      'challenge_id' => $challengeId,
      'image_src' => $src,
    ];
  }

  public static function handle(): object
  {
    $db = DB::getConnection();
    $statement = $db->prepare('DELETE FROM challenge WHERE updated_at < DATETIME("now", "-5 minutes");');
    $statement->execute();

    $challengeId = isset($_POST['challenge_id']) && is_string($_POST['challenge_id']) ? $_POST['challenge_id'] : '0';
    if (empty($challengeId)) {
      throw new Exception('No challenge_id provided.');
    }
    $rotation = isset($_POST['rotation']) && is_int($_POST['rotation']) ? $_POST['rotation'] : 0;
    $statement = $db->prepare('SELECT * FROM challenge WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $challengeId]);
    $result = (object) $statement->fetch(PDO::FETCH_ASSOC);
    if (empty($result->id)) {
      throw new Exception('Challenge expired.');
    }

    $error = abs(360 - (intval($result->rotation) + intval($rotation)));

    if ($error <= self::getMaximumPassingError()) {
      $statement = $db->prepare('UPDATE challenge SET pass = 1 WHERE id = :id');
      $statement->execute(['id' => $challengeId]);

      return (object) ['result' => 'pass'];
    }

    $statement = $db->prepare('DELETE FROM challenge WHERE id = :id');
    $statement->execute(['id' => $challengeId]);

    throw new Exception('Challenge failed.');
  }

  public static function wasPassed(string $id): bool
  {
    $db = DB::getConnection();
    $statement = $db->prepare('SELECT * FROM challenge WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $result = (object) $statement->fetch(PDO::FETCH_ASSOC);
    if (empty($result->id)) {
      throw new Exception('Challenge ID invalid/expired.');
    }
    // delete after checking, challenge only good for one request
    $statement = $db->prepare('DELETE FROM challenge WHERE id = :id');
    $statement->execute(['id' => $id]);
    if (empty($result->pass)) {
      return false;
    }
    return true;
  }

  public static function check(): object
  {
    $challengeId = isset($_POST['challenge_id']) && is_string($_POST['challenge_id']) ? $_POST['challenge_id'] : '0';
    if (self::wasPassed($challengeId)) {
      return (object) ['passed' => true];
    }

    throw new Exception('Challenge failed.');
  }
}
