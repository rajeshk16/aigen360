import { useState } from "react";
import styled from "styled-components";
import { Link } from "react-router-dom";
import { BASE_ROUTE_PATH } from "../utils/constants/basePath";

const ListItem = styled.li`
    position: relative;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 0px 15px 7px 15px;
    cursor: pointer;
    font-size: 13px;
    font-weight: ${(props) => (props.$active ? 500 : 400)};
    &:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        transform-origin: left;
        transition: all 0.3s ease-in-out;
        border-radius: 50px;
        opacity: ${(props) => (props.$active ? 1 : 0)};
        transform: ${(props) => (props.$active ? "scaleX(1)" : "scaleX(0)")};
    }
`;

const link = [
    {
        to: BASE_ROUTE_PATH,
        name: "Chat",
    },
    {
        to: `${BASE_ROUTE_PATH}/image`,
        name: "Image",
    },
    {
        to: `${BASE_ROUTE_PATH}/document`,
        name: "Document",
    },
];

const Navbar = () => {
    const [activeTab, setActiveTab] = useState("Chat");

    const handleActiveTab = (name) => {
        setActiveTab(name);
    };

    return (
        <nav className="flex justify-center">
            <ul className="flex gap-2">
                {link.map((item, index) => (
                    <Link key={index} to={item.to}>
                        <ListItem
                            onClick={() => handleActiveTab(item.name)}
                            $active={item.name === activeTab}
                            className={`after:bg-purple after:dark:bg-gold ${
                                item.name === activeTab
                                    ? "text-dark-1 dark:text-white"
                                    : "text-clr47 dark:text-gray-2"
                            }`}
                        >
                            {item.name}
                        </ListItem>
                    </Link>
                ))}
            </ul>
        </nav>
    );
};

export default Navbar;
