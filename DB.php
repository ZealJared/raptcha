<?php

class DB
{
  private static PDO|null $connection = null;

  public static function getConnection(): PDO
  {
    if (is_null(self::$connection)) {
      self::$connection = new PDO(sprintf('sqlite:%s/data/db.sqlite3', __DIR__));
      self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return self::$connection;
  }
}
