<?php

namespace Nginx;

use Nginx\NginxProcess;

class Supervisor extends NginxProcess
{
    protected static string $service = 'supervisorctl';


}