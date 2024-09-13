import { Typography } from "components/core";
import type { ProjectTech } from "constants/projects";

export const ProjectItemTech = ({ tech, icon: Icon }: ProjectTech) => {
  return (
    <li className="flex gap-[6px] align-center min-w-0 bg-primary/50 py-[6px] px-[12px] rounded-[20px]  hover:animate-pulse  transition duration-300 ease-in-out">
      <Icon className="w-[14px] h-[14px]" />
      <Typography className="text-xs truncate">{tech}</Typography>
    </li>
  );
};
