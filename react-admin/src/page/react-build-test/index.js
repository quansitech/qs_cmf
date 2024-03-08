import React, {useState, useEffect} from "react";
import { createRoot } from 'react-dom/client';
import { Select, Row, Col } from 'antd';
import reactBuildTestReq from "@/common/react-build-test-request";

const Index = function(props){
    const [stageId, setStageId] = useState(1);

    useEffect(() => {
        const init = async () => {
            const res = await reactBuildTestReq.getTest(1);
            if (parseInt(res.status) === 1){
                setStageId(res.data.stage_id);
            }
        }
        init();
    }, [])

    return <>
        <div>ReactBuildTest</div>
        <Row>
            <Col span={12}>
                <Select
                    placeholder={ "请选择" }
                    options={props.statusList}
                    onChange={(value)=>{setStageId(value)}}
                />
            </Col>
            <Col span={12}>
                <div>{props.statusList[stageId].label}</div>
            </Col>
        </Row>
    </>
}

const domNode = document.getElementById('app');
const root = createRoot(domNode);

root.render(<Index
    statusList={ statusList }
/>);