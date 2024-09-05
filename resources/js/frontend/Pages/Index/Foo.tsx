import {Head, router, usePage} from "@inertiajs/react";
import {Button} from "antd"
import style from './Foo.module.scss'

export default function () {
    const props = usePage<{
        barUrl: string
    }>().props

    const gotoBar = () => {
        router.visit(props.barUrl)
    }

    return <>
        <Head title="Foo"></Head>
        <h1 className={style.title}>TP inertia</h1>
        <div>
            <Button onClick={gotoBar}>bar</Button>
        </div>
    </>
}