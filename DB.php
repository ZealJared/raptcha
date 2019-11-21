<?php

class DB
{
  /** @var PDO */
  private static $connection;

  public static function getConnection(): PDO
  {
    if (empty(self::$connection)) {
      self::$connection = new PDO(sprintf('sqlite:%s/data/db.sqlite3', __DIR__));
      self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return self::$connection;
  }
}
