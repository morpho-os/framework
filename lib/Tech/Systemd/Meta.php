<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Systemd;

/**
 * Incapsulates meta information (knowledge) about latest systemd version. Versions <= latest may not match the meta.
 */
class Meta {
    /**
     * Returns known binary file names.
     */
    public static function knownBins(): array {
        return [
            'bootctl',
            'busctl',
            'coredumpctl',
            'homectl',
            'hostnamectl',
            'journalctl',
            'kernel-install',
            'localectl',
            'loginctl',
            'machinectl',
            'networkctl',
            'portablectl',
            'resolvectl',
            'systemctl',
            'systemd-analyze',
            'systemd-ask-password',
            'systemd-cat',
            'systemd-cgls',
            'systemd-cgtop',
            'systemd-delta',
            'systemd-detect-virt',
            'systemd-escape',
            'systemd-firstboot',
            'systemd-hwdb',
            'systemd-id128',
            'systemd-inhibit',
            'systemd-machine-id-setup',
            'systemd-mount',
            'systemd-notify',
            'systemd-nspawn',
            'systemd-path',
            'systemd-repart',
            'systemd-resolve',
            'systemd-run',
            'systemd-socket-activate',
            'systemd-stdio-bridge',
            'systemd-sysusers',
            'systemd-tmpfiles',
            'systemd-tty-ask-password-agent',
            'systemd-umount',
            'timedatectl',
            'udevadm',
            'userdbctl',
        ];
    }

    /**
     * Return known unit types. Can be got with the `systemctl --type=help --no-legend --no-pager`.
     */
    public static function knownUnitTypes(): array {
        return (new \ReflectionClass(UnitType::class))->getConstants();
    }

    /**
     * Returns known configuration section names. Can be got with the `/usr/lib/systemd/systemd --dump-configuration-items --no-pager | grep '^\[' | sort -u | tr -d '[]'
     */
    public static function knownConfSections(): array {
        return [
            'Automount',
            'Install',
            'Mount',
            'Path',
            'Scope',
            'Service',
            'Slice',
            'Socket',
            'Swap',
            'Timer',
            'Unit',
        ];
    }

    /**
     * Returns useful references to the web pages.
     */
    public static function refs(): array {
        return [
            ['text' => 'Official site', 'uri' => 'https://systemd.io/'],
            ['text' => 'Source code', 'uri' => 'https://github.com/systemd/systemd', ],
            ['text' => 'List of configuration directives', 'uri' => 'https://www.freedesktop.org/software/systemd/man/systemd.directives.html'],
        ];
    }
}
