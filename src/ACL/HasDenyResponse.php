<?php

namespace Laraveltoolkit\ACL;

use Illuminate\Auth\Access\Response;

interface HasDenyResponse
{
    /**
     * Http status code on not allowed
     */
    public function denyResponse(): Response;
}
