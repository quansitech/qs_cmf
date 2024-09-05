import {Button, Table} from "antd";
import {Link, router, usePage} from "@inertiajs/react";

export default function () {
    const props = usePage<{
        test_url: string
    }>().props

    const dataSource = [
        {
            key: '1',
            name: '胡彦斌',
            age: 32,
            address: '西湖区湖底公园1号',
        },
        {
            key: '2',
            name: '胡彦祖',
            age: 42,
            address: '西湖区湖底公园1号',
        },
    ];

    const columns = [
        {
            title: '姓名',
            dataIndex: 'name',
            key: 'name',
        },
        {
            title: '年龄',
            dataIndex: 'age',
            key: 'age',
        },
        {
            title: '住址',
            dataIndex: 'address',
            key: 'address',
        },
    ];

    const testOnly = () => {
        console.log('test only')
        router.get('', {}, {
            only: ['message'],
            onSuccess(page) {
                console.log(page)
            }
        })
    }

    return <>
        <div>
            <Table dataSource={dataSource} columns={columns}/>
            <Link href={props.test_url}>test2</Link>
            <Button onClick={testOnly}>test only</Button>
        </div>
    </>;
}