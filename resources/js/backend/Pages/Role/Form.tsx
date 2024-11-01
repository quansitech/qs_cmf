import {ProForm, ProFormText, ProFormTextArea,} from "@ant-design/pro-components";
import {Col, Form, Tree} from "antd";
import {useRef} from "react";
import uniqueId from "lodash/uniqueId";
import http from "@quansitech/antd-admin/lib/http";
import assign from "lodash/assign";

export default function (props: {
    action_list: [],
    module_list: [],
    controller_list: [],
    submit: {
        url: string,
        data: any,
    },
    initialValues?: any
}) {
    const nodeTreeList = useRef([])

    if (!nodeTreeList.current?.length) {
        props.module_list?.forEach((m: any) => {
            const mNode = {
                title: m.title,
                children: [],
                key: uniqueId('_noChecked'),
            }

            props.controller_list.filter((c: { pid: string }) => c.pid == m.id).forEach((c: any) => {
                const cNode = {
                    title: c.title,
                    children: [],
                    key: uniqueId('_noChecked'),
                }

                props.action_list.filter((a: { pid: string }) => a.pid == c.id).forEach((a: any) => {
                    cNode.children.push({
                        title: a.title,
                        key: a.id,
                    })
                })

                mNode.children.push(cNode)
            })

            nodeTreeList.current.push(mNode)
        })
    }

    return <>
        <ProForm grid={true}
                 initialValues={props.initialValues}
                 onFinish={async (values) => {
                     values.auth = values.auth.filter(a => !(a + '').match(/^_noChecked/))
                     await http({
                         method: 'post',
                         url: props.submit.url,
                         data: assign(values, props.submit.data)
                     })
                 }}
        >
            <ProFormText
                name="name"
                label="用户组名称"
                placeholder="请输入名称"
                colProps={{
                    span: 18
                }}
                rules={[
                    {required: true}
                ]}
            />
            <ProFormTextArea
                label='备注'
                name='remark'
                placeholder='请输入备注'
                colProps={{
                    span: 18
                }}
            />

            <Col span={18}>
                <Form.Item label='用户组权限'
                           name='auth'
                           valuePropName='checkedKeys'
                           trigger={'onCheck'}
                >
                    <Tree
                        height={400}
                        blockNode
                        checkable
                        selectable={false}
                        defaultExpandAll
                        treeData={nodeTreeList.current}
                        rootStyle={{
                            border: '1px solid #d9d9d9',
                        }}
                    />
                </Form.Item>
            </Col>

        </ProForm>
    </>
}