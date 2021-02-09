<?php declare(strict_types=1);
class DB
{
  private static ?PDO $connection = null;

  public static function getConnection(): PDO
  {
    if (is_null(self::$connection)) {
      $dbPath = realpath(__DIR__ . '/../data/db.sqlite3');
      self::$connection = new PDO(sprintf('sqlite:%s', $dbPath));
      self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$connection;
  }
}
