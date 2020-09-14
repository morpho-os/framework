<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Systemd;

class Meta {
    /**
     * Returns possible binary file names from the latest systemd package. NB: Versions <= latest may not contain some of these binaries.
     */
    public static function possibleBins(): array {
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
     * Return possible unit types, supported by the latest systemd version. Can be got with the `systemctl --type=help --no-legend --no-pager`.
     */
    public static function possibleUnitTypes(): array {
        return (new \ReflectionClass(UnitType::class))->getConstants();
    }

    /**
     * Returns useful references to Internet web pages.
     */
    public static function refs(): array {
        return [
            ['uri' => 'https://systemd.io/', 'text' => 'Official site'],
            ['uri' => 'https://github.com/systemd/systemd', 'text' => 'Source code'],
        ];
    }
}
