import {Head, router, usePage} from "@inertiajs/react";
import {Button} from "antd"

export default function () {
    const props = usePage<{
        fooUrl: string,
    }>().props

    const gotoFoo = () => {
        router.visit(props.fooUrl)
    }

    return <>
        <Head title="Bar"></Head>
        <h1>Bar</h1>
        <div>
            <Button onClick={gotoFoo}>foo</Button>
        </div>
    </>
}