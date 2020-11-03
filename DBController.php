<?php

class DBController
{
    /**
     * Данные от БД
     */
    const USER = 'root';
    const PASSWORD = '18212515Sh!';
    const HOST = 'localhost';
    const DB_NAME = 'rbc';

    /**
     * Текущее соединение
     * @var null
     */
    private static $connection = null;

    private static function connectToDB()
    {
        self::$connection = new mysqli(self::HOST, self::USER, self::PASSWORD, self::DB_NAME);
    }

    /**
     * @return null
     */
    public static function getConnection()
    {
        if (is_null(self::$connection)) self::connectToDB();

        return self::$connection;
    }

    /**
     * Выполняет передаваемый в метод sql запрос
     * @param $sql
     * @return array|bool
     */
    public static function Query($sql)
    {
        if (is_null(self::$connection)) self::connectToDB();

        if (self::$connection->connect_errno) {
            return false;
        }

        if (!$result = self::$connection->query($sql)) {
            return false;
        }

        if ($result->num_rows === 0) {
            return [];
        }

        return $result;
    }

    public static function closeConnection()
    {
        if (!is_null(self::$connection)) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}