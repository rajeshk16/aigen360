import { ImageIcon } from "./icons";
import cn from "../utils/cn";

const exampleComponent = ({ className }) => {
	return (
		<div
			className={cn(
				"relative rounded-b-lg image-skeleton-gradient h-[316px] w-full flex items-center justify-center animate-pulse",
				className
			)}
		>
			<div className="absolute top-0 w-full">
				<div className="linear-progressbar"></div>
			</div>
			<ImageIcon />
		</div>
	);
};

export default exampleComponent;
