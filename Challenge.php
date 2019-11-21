<?php

class Challenge
{
  public static function getMaximumPassingError(): int
  {
    return 2;
  }

  public static function saveImage(): object
  {
    $jsonData = file_get_contents('php://input');
    if (empty($jsonData)) {
      $jsonData = '';
    }
    $image = json_decode($jsonData)->imageDataUrl ?? false;
    if ($image) {
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
    $rotateDegrees = rand(0, 359);
    $imagick->rotateImage('#00000000', $rotateDegrees);
    $newWidth = $imagick->getImageWidth();
    $newHeight = $imagick->getImageHeight();
    $imagick->setImagePage($newWidth, $newHeight, 0, 0);
    $imagick->cropImage(300, 300, ($newWidth - 300) / 2, ($newHeight - 300) / 2);
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
    $statement = $db->prepare('DELETE FROM challenge WHERE created_at < DATETIME("now", "-5 minutes");');
    $statement->execute();

    $challengeId = filter_input(INPUT_POST, 'challenge_id');
    if (empty($challengeId)) {
      throw new Exception('No challenge_id provided.');
    }
    $rotation = filter_input(INPUT_POST, 'rotation', FILTER_VALIDATE_INT);
    if (false === $rotation) {
      throw new Exception('No rotation provided.');
    }

    $statement = $db->prepare('SELECT * FROM challenge WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $challengeId]);
    $result = (object) $statement->fetch(PDO::FETCH_ASSOC);
    if (empty($result->id)) {
      throw new Exception('Challenge expired.');
    }

    $error = abs(360 - ($result->rotation + $rotation));

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
    if (empty($result->pass)) {
      return false;
    }

    return true;
  }

  public static function check(): object
  {
    $challengeId = filter_input(INPUT_POST, 'challenge_id');
    if (self::wasPassed($challengeId)) {
      return (object) ['passed' => true];
    }

    throw new Exception('Challenge failed.');
  }
}
