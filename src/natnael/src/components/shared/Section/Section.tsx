import type { ReactElement } from "react";

import { Typography } from "components/core";

interface SectionProps {
  children: ReactElement | ReactElement[];
  id: string;
  headingText: string;
}

export const Section = ({ children, id, headingText }: SectionProps) => {
  return (
    <section id={id} className="flex flex-col">
      <div className="flex flex-col gap-[40px] w-full my-[84px] mx-auto sm:my-[124px]">
        <div className="animate-hidden flex flex-col w-full h-full">
          <Typography tag="h2" weight="semibold" className="text-5xl sm:text-7xl hover:text-negus  transition duration-150 ease-in-out hover:animate-pulse">
            {headingText}
          </Typography>
        </div>
        {children}
      </div>
    </section>
  );
};
