<?php

namespace Laraveltoolkit\SEO;

enum RobotRule: string
{
    case ALL = 'all';
    case NOINDEX = 'noindex';
    case NOFOLLOW = 'nofollow';
    case NONE = 'none';
    case NOARCHIVE = 'noarchive';
    case NOSITELINKSSEARCHBOX = 'nositelinkssearchbox';
    case NOSNIPPET = 'nosnippet';
    case INDEXIFEMBEDDED = 'indexifembedded';
    case MAX_SNIPPET = 'max-snippet';
    case MAX_IMAGE_PREVIEW = 'max-image-preview';
    case MAX_VIDEO_PREVIEW = 'max-video-preview';
    case NOTRANSLATE = 'notranslate';
    case NOIMAGEINDEX = 'noimageindex';
    case UNAVAILABLE_AFTER = 'unavailable_after';
}
