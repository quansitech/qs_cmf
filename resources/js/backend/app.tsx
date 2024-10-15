import {createInertiaApp, router} from '@inertiajs/react';
import {createRoot} from 'react-dom/client';
import Layout from "@quansitech/antd-admin/components/Layout";
import {ReactNode} from "react";
import "@quansitech/antd-admin/lib/container";
import {App as AntdApp} from "antd";

router.on('invalid', ev => {
    // 返回html时直接跳转
    if (ev.detail.response.headers["content-type"].includes("text/html")) {
        ev.preventDefault()
        window.location.href = ev.detail.response.config.url as string
    }
})

createInertiaApp({
    resolve: async name => {
        const pages = import.meta.glob('./Pages/**/*.tsx')
        const page: any = await pages[`./Pages/${name}.tsx`]()
        page.default.layout = page.default.layout || ((page: ReactNode) => <Layout children={page}/>)

        return page
    },
    setup({el, App, props}) {
        createRoot(el).render(
            <AntdApp>
                <App {...props} />
            </AntdApp>
        )
    },
})