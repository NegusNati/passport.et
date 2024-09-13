import ExternalLinkIcon from "icons/ExternalLinkIcon";

export const ProjectItemExternalLink = () => {
  return (
    <button
      tabIndex={-1}
      aria-label="Visit project page"
      className="absolute top-[-12px] right-[-12px] grid place-items-center w-[30px] h-[30px] bg-[#fff] rounded-full z-2 text-background1 [&>svg]:w-[20px] [&>svg]:h-[20px] duration-200 shadow-[0px_1px_7px_theme(colors.background1)] opacity-0 pointer-events-none invisible translate-y-[10px] scale-[0.95] group-hover/imgLink:opacity-100 group-hover/imgLink:pointer-events-all group-hover/imgLink:transform-none group-hover/imgLink:visible group-focus-visible/imgLink:opacity-100 group-focus-visible/imgLink:pointer-events-all group-focus-visible/imgLink:transform-none group-focus-visible/imgLink:visible"
    >
      <ExternalLinkIcon />
    </button>
  );
};
