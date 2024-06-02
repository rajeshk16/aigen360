import { useEffect } from "react";
import { useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import TabPanel from "./TabPanel";
import { LAYOUT } from "../constants/layout";
import ChatGenerateBy from "./ChatGenerateBy";
import ContinueWithLast from "./ContinueWithLast";
import { setLayout } from "../store/slices/uiSlice";
import { EditNoteIcon, PictureIcon, BrowserIcon } from "./icons";
import { BASE_ROUTE_PATH } from "../utils/constants/basePath";

const ChatPanel = () => {
    const navigate = useNavigate();
    const dispatch = useDispatch();
    const handleNavigate = (to) => {
        navigate(`${BASE_ROUTE_PATH}${to}`);
    };
    useEffect(() => {
        dispatch(setLayout(LAYOUT.CHAT));
    }, [dispatch]);

    return (
        <TabPanel className="relative h-full flex flex-col items-center justify-center gap-3">
            {[
                {
                    to: "/web",
                    title: "Chat with webpage",
                    subtitle: "Enter the URL of the page you want to discuss",
                    icon: <BrowserIcon />,
                },
                {
                    to: "/document",
                    title: "Chat with your document",
                    subtitle: "pdf, doc or docx",
                    icon: <EditNoteIcon />,
                },
                {
                    to: "/image",
                    title: "Generate Images",
                    subtitle: "Effective and large prompts for better image",
                    icon: <PictureIcon />,
                },
            ].map((item, i) => (
                <ChatGenerateBy
                    key={i}
                    i={i}
                    onClick={() => handleNavigate(item.to)}
                    title={item.title}
                    subtitle={item.subtitle}
                    icon={item.icon}
                />
            ))}
            <p className="text-center text-gray-1 before:absolute before:h-px before:w-2.5 before:bg-gray-1 before:mt-3 before:-ml-4 after:absolute after:h-px after:w-2.5 after:bg-gray-1 after:mt-3 after:ml-1">
                or
            </p>
            <ContinueWithLast />
        </TabPanel>
    );
};

export default ChatPanel;
