namespace Morpho.System {
    class ResourceLoader {
        public static loadStyle(uri: string): void {

        }

        public static loadScript(uri: string): void {
            // @TODO:
            let node = document.createElement('script');
            node.type = 'text/javascript';
            node.charset = 'utf-8';
            //node.async = true;
            document.getElementsByTagName('head')[0].appendChild(node);
        }

        public static loadImage(uri: string): void {

        }

        // @TODO: loadFont?
    }
}