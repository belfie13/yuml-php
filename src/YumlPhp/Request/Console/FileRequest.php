<?php


namespace YumlPhp\Request\Console;

use YumlPhp\Request\FileRequest as BaseRequest;

/**
 * FileRequest
 * @author Robert Schönthal <seroscho@gmail.com>
 */
class FileRequest extends BaseRequest
{

    public function build()
    {
        return explode(',', $this->getData());
    }
}