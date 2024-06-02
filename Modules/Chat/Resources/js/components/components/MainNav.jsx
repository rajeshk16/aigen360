import {  AccountIcon,  DahboardNavIcon, DashboardIcon, GalleryNavIcon, GoogleHomeIcon, HistoryNavIcon, ImageNavIcon, SpeechToTextNavIcon, VoiceOverIcon } from "./icons";
import CodeNavIcon from "./icons/CodeNavIcon";
import GradientChatIcon from "./icons/GradientChatIcon";
import { useEffect, useState } from "react";
import { Switch } from '@headlessui/react'
import { toggleTheme } from "../store/slices/themeSlice";
import { useDispatch, useSelector } from "react-redux";
import {BASE_MAIN_NAV_PATH} from "../utils/constants/basePath";


const MainNav = () => {
    const { theme } = useSelector((state) => state.theme);
    const [enabled, setEnabled] = useState(theme === "light" ? false : true);
    const dispatch = useDispatch();

    const navItems = [
        {
          type: 'header',
          items: [
            
            {
                title: 'Dashboard',
                 Icon: <DahboardNavIcon />,
                url: 'user/dashboard'
              },
          ],
        },
        {
          type: 'contents',
          items: [
            {
                title: 'Prebuilt Templates',
                Icon: <DashboardIcon />,
                url: 'user/templates'
            },
            {
              title: 'Image',
              Icon: <ImageNavIcon />,
              url: 'user/image'
            },
            {
              title: 'Code',
              Icon: <CodeNavIcon />,
              url: 'user/code'
            },
            {
              title: 'Speech to Text',
              Icon: <SpeechToTextNavIcon />,
              url: 'user/speech-to-text'
            },
            {
              title: 'Voiceover',
              Icon: <VoiceOverIcon />,
              url: 'user/text-to-speech'
            },
            {
              title: 'Chat',
              Icon: <GradientChatIcon />,
              url: 'user/Chat'
            },
          ],
        },
        {
            type: 'records',
            items: [
              {
                title: 'History',
                Icon: <HistoryNavIcon />,
                url: 'user/documents'
              },
              {
                title: 'Gallery',
                Icon: <GalleryNavIcon />,
                url: 'user/image-gallery'
              },
            ],
          },
          
        {
            type: 'footer',
            items: [
              {
                title: 'Account',
                Icon: <AccountIcon />,
                url: 'user/profile'
              },
            ],
          },

      ];

    useEffect(()=>{
        handleThemeChange();
    },[enabled])

    const handleThemeChange=()=>{
        dispatch(toggleTheme())
    }


  return (
    <div className="h-full bg-white dark:bg-dark-shade-1 static pt-1.5 flex flex-col">
         {navItems.map((section, index) => (
            <div className={`flex flex-col justify-center align-center`} key={index} >
                {section.items.map((item, itemIndex) => (
                     <a href={item.title !== 'Chat' ? `${BASE_MAIN_NAV_PATH}/${item?.url}` : undefined}  key={itemIndex}>
                     <NavItem
                         title={item.title}
                         Icon={item.Icon}
                     />
                    </a>
                ))}
                {
                    index != navItems.length - 1  &&
                    <div className="border-t  border-gray-2 dark:border-clr47 w-14 my-2 mx-2"></div>
                }
                
            </div>
        ))}
        <div className="mt-auto pl-2 h-[62px] flex items-center border-t border-t-gray-2 dark:border-t-clr47 mx-2">
            <Switch
                checked={enabled}
                onChange={setEnabled}
                className={`${enabled ? 'bg-orange' : 'bg-gray-2'}
                relative inline-flex h-[20px] w-[36px] shrink-0 cursor-pointer rounded-full border-1 border-gray-1 transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2  focus-visible:ring-white/75`}
            >
                <span className="sr-only">Use setting</span>
                <span
                aria-hidden="true"
                className={`${enabled ? 'translate-x-4' : 'translate-x-0'} 
                    pointer-events-none inline-block h-[20px] w-[20px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out`}
                />
          </Switch>
        </div>

    </div>
  )
}



export default MainNav;

const NavItem = ({
    title,
    Icon,
}) => {
  
    return (
        <button className={`group/item relative h-[52px] w-full flex items-center gap-3 ${title == 'Artifism' ? 'pl-5' : 'pl-6'} pr-4  hover:bg-bg-1 dark:hover:bg-clr47 transition duration-200 ease-out  ${title =='Chat' && 'gradient-border-r__active' }`}>
            <div className={`flex-shrink-0`}>
                {Icon}
            </div>
            <span className={`block invisible group-hover/item:visible dark:bg-white bg-clr47 dark:text-dark-1 text-white  rounded-md z-[700] px-2 py-[5px] font-medium text-xs absolute  transition-all duration-200 ease-out opacity-0 group-hover/item:opacity-100 whitespace-nowrap ${title == 'Artifism' ? 'group-hover/item:translate-x-10' : 'group-hover/item:translate-x-8'}`}>{title}</span>
              
        </button>
    );
};
