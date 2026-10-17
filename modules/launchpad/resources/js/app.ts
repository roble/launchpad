import { registerIcon } from '@saucebase/vue/lib/navigation';
import IconRocket from '~icons/lucide/rocket';

/**
 * Launchpad module setup
 * Called during app initialization before mounting
 */
export function setup() {
    registerIcon('launchpad', IconRocket);
}
