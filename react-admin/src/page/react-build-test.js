import reactBuildTestReq from "../common/react-build-test-request";

const useState = React.useState;
const useEffect = React.useEffect;


const ReactBuildTest = function(props){
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

ReactDOM.render(<ReactBuildTest
    statusList={ statusList }
/>, document.getElementById('app'));