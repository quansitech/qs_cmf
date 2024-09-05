import {createInertiaApp} from '@inertiajs/react'
import {createRoot} from 'react-dom/client'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.tsx')
        return pages[`./Pages/${name}.tsx`]()
    },
    setup({el, App, props}) {
        createRoot(el).render(<App {...props} />)
    },
})