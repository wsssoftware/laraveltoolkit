<?php

use Laraveltoolkit\Facades\Regex;

it('can get hashtags', function () {
    $payload = 'Explore the beauty of nature and embark on adventures that inspire. Live your best life! #travel #nature #explore #adventure #inspiration #wanderlust #life #journey #outdoors #discover';
    expect(Regex::getHashtags($payload))
        ->toContain(...[
            '#travel', '#nature', '#explore', '#adventure', '#inspiration', '#wanderlust', '#life', '#journey',
            '#outdoors', '#discover',
        ])
        ->not->toContain(...['#foo', '#bar']);
});

it('test a valid isEmail', function ($email) {
    expect(Regex::isEmail($email))
        ->toBeTrue();
})->with([
    'john.doe@example.com',
    'sarah.smith@domain.org',
    'michael.brown@company.net',
    'emma.jones@webmail.co',
    'logan.mitchell@development.io',
    'harper.rogers@analytics.biz',
]);

it('test an invalid isEmail', function ($email) {
    expect(Regex::isEmail($email))
        ->toBeFalse();
})->with([
    'john.doe@.com',             // Falta o nome do domínio
    'sarah.smith@domain',        // Falta a extensão do domínio
    '@company.net',              // Falta o nome do usuário
    'emma.jones@webmail..co',    // Dois pontos seguidos no domínio
    'david.wilson@business_com', // Underscore (_) não é permitido no domínio
    'laura miller@mailservice.info', // Espaço no nome do usuário
    'james.taylor@corporate.biz.', // Ponto no final do domínio
    'olivia.moore@customdomain', // Falta a extensão do domínio
    'daniel@anderson@provider.io', // Dois arrobas
    'sophia.thompson@techhub.dev/extra', // Barra na extensão do domínio
]);

it('test a valid isHexColor', function ($color) {
    expect(Regex::isHexColor($color))
        ->toBeTrue();
})->with([
    '#FF5733', // Vibrant Orange
    '#33FF57', // Bright Green
    '#33FFCC',  // Teal
]);

it('test an invalid isHexColor', function ($color) {
    expect(Regex::isHexColor($color))
        ->toBeFalse();
})->with([
    '#FF573Z',   // Contém um caractere inválido 'Z'
    '#33FF5',    // Apenas 5 caracteres
    '3357FF',    // Falta o símbolo '#'
    '#FF33A10',  // Contém 7 caracteres (um extra)
    '#33FFF',    // Apenas 5 caracteres
    '#XYZ123',   // Contém caracteres inválidos 'XYZ'
    '##FF5733',  // Dois símbolos '#'
    '#123ABCG',  // Contém 7 caracteres e um caractere inválido 'G'
    '#12 34FF',  // Contém um espaço
    '#FF57333',  // Contém 7 caracteres (um extra)
]);

it('test a valid isIPv4Address', function ($ip) {
    expect(Regex::isIPv4Address($ip))
        ->toBeTrue();
})->with([
    '192.168.0.1',
    '10.0.0.1',
    '172.16.254.1',
    '8.8.8.8',
    '192.0.2.146',
    '127.0.0.1',
]);

it('test an invalid isIPv4Address', function ($ip) {
    expect(Regex::isIPv4Address($ip))
        ->toBeFalse();
})->with([
    '192.168.0.256',    // O último octeto é maior que 255
    '10.0.0.1.1',       // Tem um octeto extra
    '172.16.300.1',     // Um octeto é maior que 255
    '203.0.113.-5',     // Um octeto é negativo
    '8.8.8',            // Apenas 3 octetos
    '192.0.2.146.1',    // Tem um octeto extra
    '198.51.100.23.0',  // Tem um octeto extra
    '127.0.0.1.1.1',    // Muitos octetos
    '255.255.255.256',  // Um octeto é maior que 255
    '216.58.211.142.0',  // Tem um octeto extra
]);

it('test a valid isIPv6Address', function ($ip) {
    expect(Regex::isIPv6Address($ip))
        ->toBeTrue();
})->with([
    '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
    '2001:db8:0:1234:0:567:8:1',
    'fe80::1ff:fe23:4567:890a',
    '::1',
    '::',
    '2001:0db8:0000:0042:0000:0000:0000:0000',
    '2001:db8::42:8844',
    '2607:f0d0:1002:51::4',
    '2001:0db8:0000:0000:0000:0000:0000:0001',
    '::ffff:192.168.1.1',
]);

it('test an invalid isIPv6Address', function ($ip) {
    expect(Regex::isIPv6Address($ip))
        ->toBeFalse();
})->with([
    '2001:db8:::1',
    '2001:db8:12345:4567:890a',
    'fe80::1ff:fe23:4567:890g',
    '::1::2',
    '12345:6789:abcd:ef01:2345:6789:abcd:ef01',
    '2001:db8:85a3:0000:0000:8a2e:0370:7334:1234',
    '::ffff::192.168.1.1',
]);

it('test a valid isIPAddress', function ($ip) {
    expect(Regex::isIPAddress($ip))
        ->toBeTrue();
})->with([
    '10.0.0.1',
    '203.0.113.5',
    '8.8.8.8',
    '127.0.0.1',
    '255.255.255.0',
    '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
    '2001:db8:0:1234:0:567:8:1',
    'fe80::1ff:fe23:4567:890a',
    '::1',
    '::',
    '2001:0db8:0000:0042:0000:0000:0000:0000',
    '2001:db8::42:8844',
    '2607:f0d0:1002:51::4',
    '2001:0db8:0000:0000:0000:0000:0000:0001',
    '::ffff:192.168.1.1',
]);

it('test an invalid isIPAddress', function ($ip) {
    expect(Regex::isIPAddress($ip))
        ->toBeFalse();
})->with([
    '2001:db8:::1',
    '2001:db8:12345:4567:890a',
    'fe80::1ff:fe23:4567:890g',
    '::1::2',
    '12345:6789:abcd:ef01:2345:6789:abcd:ef01',
    '2001:db8:85a3:0000:0000:8a2e:0370:7334:1234',
    '::ffff::192.168.1.1',
    '192.168.0.256',    // O último octeto é maior que 255
    '10.0.0.1.1',       // Tem um octeto extra
    '172.16.300.1',     // Um octeto é maior que 255
    '203.0.113.-5',     // Um octeto é negativo
    '8.8.8',            // Apenas 3 octetos
    '192.0.2.146.1',    // Tem um octeto extra
    '198.51.100.23.0',  // Tem um octeto extra
    '127.0.0.1.1.1',    // Muitos octetos
    '255.255.255.256',  // Um octeto é maior que 255
    '216.58.211.142.0',  // Tem um octeto extra
]);

it('test a valid isLikePhpVariableChars', function ($var) {
    expect(Regex::isLikePhpVariableChars($var))
        ->toBeTrue();
})->with(['foo_bar1', 'fooBar2', 'foo_bar_3', 'foo_barCar_4']);

it('test an invalid isLikePhpVariableChars', function ($var) {
    expect(Regex::isLikePhpVariableChars($var))
        ->toBeFalse();
})->with(['1fooBar3', '$fooBar3', '$@@@#$', 'foo_$_dsa', 'foo_#da']);

it('test a valid isSequenceOfUniqueChar', function ($char) {
    expect(Regex::isSequenceOfUniqueChar($char))
        ->toBeTrue();
})->with(['a', 'bbbbbbbb', '333333', '#######']);

it('test an invalid isSequenceOfUniqueChar', function ($char) {
    expect(Regex::isSequenceOfUniqueChar($char))
        ->toBeFalse();
})->with(['abcde', 'ababab', 'aaaAAAA', '$%^&*']);

it('test a valid isURL', function ($url) {
    expect(Regex::isURL($url))
        ->toBeTrue();
})->with([
    'https://www.example.com',
    'http://example.org',
    'https://subdomain.example.com/path/to/resource',
    'ftp://ftp.example.com/file.txt',
    'http://localhost:8080',
    'https://example.co.uk',
    'http://www.example.com:3000/path?query=string',
    'https://example.com/#fragment',
    'http://example.com/some-page',
    'https://www.example.com/path/to/page?param=value&param2=value2',
]);

it('test an invalid isURL', function ($url) {
    expect(Regex::isURL($url))
        ->toBeFalse();
})->with([
    'htp://www.example.com',               // Protocolo inválido
    'http:/example.com',                   // Protocolo incompleto
    '://example.com',                      // Protocolo ausente
    'http://www.example..com',             // Domínio com pontos consecutivos
    'http://example.c',                    // TLD inválido (muito curto)
    'http://.com',                         // Domínio ausente
    'www.example.com',                     // Sem protocolo
    'http://example.com:abcd',             // Porta inválida (não numérica)
    'http://example.com/path with spaces',  // Caminho com espaços
]);

it('return only alpha', function () {
    expect(Regex::onlyAlpha('A$@%^&@*62137321aa7766687bb'))
        ->toBe('Aaabb')
        ->and(Regex::onlyAlpha('A1ã2Ã3Ê4Í5ó'))
        ->toBe('AãÃÊÍó')
        ->and(Regex::onlyAlpha('A1ã2Ã3Ê4Í5ó', allowAccents: false))
        ->toBe('A')
        ->and(Regex::onlyAlpha('A $@%^&@*62137321a a 7766687b b', true))
        ->toBe('A a a b b');
});

it('return only alphanumeric', function () {
    expect(Regex::onlyAlphaNumeric('A$@%^&@*62137321aa7766687bb'))
        ->toBe('A62137321aa7766687bb')
        ->and(Regex::onlyAlphaNumeric('A1ã2Ã3Ê4Í5ó'))
        ->toBe('A1ã2Ã3Ê4Í5ó')
        ->and(Regex::onlyAlphaNumeric('A1ã2Ã3Ê4Í5ó', allowAccents: false))
        ->toBe('A12345')
        ->and(Regex::onlyAlphaNumeric('A $@%^&@*62137321a a 7766687b b', true))
        ->toBe('A 62137321a a 7766687b b');
});

it('return only numeric', function () {
    expect(Regex::onlyNumeric('A$@%^&@*62137321aa7766687bb'))
        ->toBe('621373217766687')
        ->and(Regex::onlyNumeric('A1ã2Ã3Ê4Í5ó'))
        ->toBe('12345')
        ->and(Regex::onlyNumeric('A1ã2Ã3Ê4Í5ó'))
        ->toBe('12345')
        ->and(Regex::onlyNumeric('A $@%^&@*62137321a a 7766687b b', true))
        ->toBe(' 62137321  7766687 ');
});
