import 'src/module/sw-cms/service/cms.service';
import '../index';
import 'src/module/sw-cms/mixin/sw-cms-element.mixin';
import './index';
import { mount } from '@vue/test-utils';

async function createWrapper() {
    return mount(await wrapTestComponent('sw-cms-el-config-html', {
        sync: true,
    }), {
        global: {
            provide: {
                cmsService: Shopware.Service('cmsService'),
            },
        },
        props: {
            element: {
                config: {
                    content: {
                        value: 'Test',
                    },
                },
            },
        },
    });
}

describe('src/module/sw-cms/elements/html/config/index.js', () => {
    let wrapper;

    beforeEach(async () => {
        wrapper = await createWrapper();

        await flushPromises();
    });

    afterEach(async () => {
        if (wrapper) {
            await wrapper.unmount();
        }

        await flushPromises();
    });

    it('should be a Vue.js component', () => {
        expect(wrapper.vm).toBeTruthy();
    });

    it('should update element onBlur', () => {
        wrapper.vm.onBlur('Foo');
        expect(wrapper.vm.element.config.content.value).toBe('Foo');
        expect(wrapper.emitted('element-update')).toBeTruthy();
    });

    it('should not update element onInput', () => {
        wrapper.vm.onInput('Test');
        expect(wrapper.vm.element.config.content.value).toBe('Test');
        expect(wrapper.emitted('element-update')).toBeFalsy();
    });
});
