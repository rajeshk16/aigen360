import HistoryIcon from "./icons/HistoryIcon";

const Chip = () => {
	return (
		<div className="flex items-center gap-1.5 bg-white dark:bg-dark-shade-1 dark:border dark:border-clr47 w-max px-2.5 py-1.5 rounded-lg">
			<HistoryIcon />
			<p className="text-sm font-normal text-dark-1 dark:text-white">
				History
			</p>
		</div>
	);
};

export default Chip;
