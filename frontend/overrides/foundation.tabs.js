'use strict';

import { Tabs } from 'foundation-sites/js/foundation.tabs';

/**
 * Correctly handle history changing if the page has `<base>` tag
 */
export default class LocationAwareTabs extends Tabs {
    /**
     * @inheritDoc
     */
    _handleTabChange($target, historyHandled) {

        // With `activeCollapse`, if the target is the active Tab, collapse it.
        if ($target.hasClass(`${this.options.linkActiveClass}`)) {
            if(this.options.activeCollapse) {
                this._collapse();
            }
            return;
        }

        var $oldTab = this.$element.
            find(`.${this.options.linkClass}.${this.options.linkActiveClass}`),
            $tabLink = $target.find('[role="tab"]'),
            target = $tabLink.attr('data-tabs-target'),
            anchor = target && target.length ? `#${target}` : $tabLink[0].hash,
            $targetContent = this.$tabContent.find(anchor);

        //close old tab
        this._collapseTab($oldTab);

        //open new tab
        this._openTab($target);

        //either replace or update browser history
        if (this.options.deepLink && !historyHandled) {
            if (this.options.updateHistory) {
                history.pushState({}, '', location.pathname + location.search + anchor);
            } else {
                history.replaceState({}, '', location.pathname + location.search + anchor);
            }
        }

        /**
         * Fires when the plugin has successfully changed tabs.
         * @event Tabs#change
         */
        this.$element.trigger('change.zf.tabs', [$target, $targetContent]);

        //fire to children a mutation event
        $targetContent.find("[data-mutate]").trigger("mutateme.zf.trigger");
    }
}
