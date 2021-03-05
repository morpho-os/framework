///<amd-module name="localhost/lib/base/keyboard" />

import * as bindKey_ from "keymaster"

export function bindKey(key: string, handler: KeyHandler): void {
    bindKey_(key, handler);
}
