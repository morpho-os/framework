///<amd-module name="localhost/lib/base/grid" />

import {Widget} from "./widget";

type RowAndEntityIdResult = [JQuery, EntityId];

export abstract class Grid extends Widget {
    protected normalizeConf(conf: {}) {
        return conf;
    }

/*    protected init(): void {
        super.init();
        if ($(this).attr('id') === undefined) {

        }
    }*/

    public addRow<TEntity>(entity: TEntity): void {
        const tpl = this.rowTpl(entity);
        this.el.find('tr:last').after(tpl.content);
    }

    public updateRow<TEntity>(entity: TEntity & {id: EntityId}): void {
        const id = entity.id;
        const tpl = this.rowTpl(entity);
        this.el.find('#entity' + id).replaceWith(tpl.content);
    }

    protected rowAndEntityId(clickedEl: HTMLElement): RowAndEntityIdResult {
        const $row = $(clickedEl).closest('tr');
        const entityId = <EntityId>(<string>$row.attr('id')).split('-').pop();
        return [$row, entityId];
    }

    protected rowTpl<TEntity>(entity: TEntity & {[key: string]: any}): HTMLTemplateElement {
        const rowTplClone = <HTMLTemplateElement>$(this.el.attr('id') + '-row-tpl')[0].cloneNode(true);

        console.log(rowTplClone)
        /*
        const re = new RegExp('(' + Object.keys(entity).map(key => '\\$' + key).join('|') + ')', 'g')
        rowTplClone.innerHTML = rowTplClone.innerHTML.replace(re, function (match) {
            const key = match.substr(1);
            if (typeof entity[key] === 'object') {
                return entity[key]
            }
            return String(entity[key]).e();
        });
         */
        function replacePlaceholders(html: string, entity: any, prefix: string, suffix: string): string {
            for (const [key, val] of Object.entries(entity)) {
                if (typeof val === 'object') {
                    // NB: works only for 1 level of depth, for unification add `depth` parameter.
                    html = replacePlaceholders(html, val, prefix + key + '[', ']')
                } else {
                    html = html.replace(prefix + key + suffix, String(val).e());
                }
            }
            return html;
        }
        rowTplClone.innerHTML = replacePlaceholders(rowTplClone.innerHTML, entity, '$', '');
        return rowTplClone;
    }
}
