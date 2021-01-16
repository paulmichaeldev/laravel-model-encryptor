<?php

namespace PmIngram\Laravel\ModelEncryptor;

use \Illuminate\Encryption\Encrypter;

trait HasEncryption
{
    public static function bootHasEncryption()
    {
        self::creating(function ($model) {
            if ($model->encryptOnCreate === null || $model->encryptOnCreate === true) $model->encrypt();
        });
    }

    public function encrypt(bool $update = false) : void
    {
        $this->do('encrypt', $update);
    }

    public function decrypt(bool $update = false) : void
    {
        $this->do('decrypt', $update);
    }


    private function do(string $direction = 'encrypt', bool $update = false) : void
    {
        $modelEncryptionKey = $this->encryptionKey ?? '';

        $saltColumn = $this->saltColumn ?? '';
        $saltData = $this->$saltColumn ?? '';

        $dataEncryptionKey = md5(config('app.key') . $modelEncryptionKey . $saltData);
        $encrypter = new Encrypter($dataEncryptionKey, config('app.cipher'));

        $columnKeys = $this->columnKeys ?? [];

        foreach ($columnKeys as $columnKey) {
            if (empty($this->$columnKey)) continue;

            try {
                $this->$columnKey = $encrypter->$direction($this->$columnKey);
            } catch (\Exception $e) {
                \Log::error("ModelEncryptor: " . class_basename($this) . " {$direction} PK: {$this->getKey()} - {$e->getMessage()}");
            }
        }

        if ($update) $this->save();
    }
}
