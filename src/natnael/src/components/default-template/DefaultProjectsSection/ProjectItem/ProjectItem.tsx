import { Link } from "react-router-dom";

import { IconButton, Typography } from "components/core";
import type { Project } from "constants/projects";
import GithubIcon from "icons/GithubIcon";
import PinIcon from "icons/PinIcon";

import { ProjectItemExternalLink } from "./ProjectItemExternalLink";
import { ProjectItemTech } from "./ProjectItemTech";

export const ProjectItem = ({
  title,
  description,
  image,
  url,
  githubUrl,
  techs,
  isPinned
}: Project) => {
  return (
    <div className="group animate-hidden relative flex flex-col gap-[20px] pb-[10px]">
      {isPinned && (
        <div className="absolute top-[-8px] left-[-8px] text-primary z-5 pointer-events-none [&>svg]:w-[24px] [&>svg]:h-[24px] [&>svg]:rotate-[270deg]">
          <PinIcon />
        </div>
      )}
      <Link
        className="group/imgLink relative w-full"
        to={url}
        target="_blank"
        rel="noopener noreferrer"
      >
        <div className="relative max-w-full rounded-lg pb-[75%] overflow-hidden duration-200 shadow-[0px_4px_7px_theme(colors.background2)] group-hover:opacity-50 group-focus-visible/imgLink:shadow-[0px_0px_0px_2px_theme(colors.primary)]">
          <img
            className="absolute left-0 top-0 h-full w-full object-cover select-none duration-300 group-hover:scale-[1.015]"
            alt={title}
            src={image}
            loading="lazy"
            draggable="false"
          />
        </div>
        <ProjectItemExternalLink />
      </Link>
      <div className="flex flex-col gap-[12px] whitespace-pre-line">
        {isPinned && (
          <Typography className="text-primary text-l" weight="bold">
            Pinned
          </Typography>
        )}
        <Typography className="text-xl">{title}</Typography>
        <Typography className="text-color2">{description}</Typography>
        <div className="grid grid-cols-[1fr_auto] items-start gap-[12px] [&>a]:mt-[-5px]">
          <ul className="inline-flex flex-wrap align-center gap-[12px] ">
            {techs.map((projectTech) => (
              <ProjectItemTech key={projectTech.tech} {...projectTech}  />
            ))}
          </ul>
          {githubUrl && (
            <Link to={githubUrl} target="_blank" rel="noopener noreferrer" tabIndex={-1}>
              <IconButton title="GitHub Repository">
                <GithubIcon />
              </IconButton>
            </Link>
          )}
        </div>
      </div>
    </div>
  );
};
