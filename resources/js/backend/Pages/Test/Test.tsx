import {Link, usePage} from "@inertiajs/react";
import styles from './Test.module.scss'

export default function () {
    const props = usePage<{
        index_url: string
    }>().props

    return <>
        <div>
            <h1 className={styles.title}>Test</h1>
            <Link href={props.index_url}>index</Link>
        </div>
    </>;
}