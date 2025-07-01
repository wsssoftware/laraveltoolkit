<?php

namespace Laraveltoolkit\Flash;

enum Severity: string
{
    case SUCCESS = 'success';
    case INFO = 'info';
    case WARN = 'warn';
    case ERROR = 'error';
    case SECONDARY = 'secondary';
    case CONTRAST = 'contrast';
}
