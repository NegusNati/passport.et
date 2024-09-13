import { Link } from "react-router-dom";

import { skills } from "constants/skills";
import { Section } from "shared/Section";

export const DefaultSkillsSection = () => {
  return (
    <Section id="skills" headingText="Skills">
      <div className="flex flex-col gap-[40px] w-full">
        <div className="flex flex-col gap-[30px]">
          <h3 className="animate-hidden text-xl font-semibold">Experienced with</h3>
          <div className="grid grid-cols-[repeat(auto-fill,minmax(145px,_1fr))] w-full gap-[30px] sm:grid-cols-3 lg:grid-cols-4">
            {skills.experiencedWith.map(({ src, website, text }) => (
              <div className="animate-hidden flex flex-col gap-[10px]" key={website}>
                <Link
                  className="group flex items-center gap-[8px]"
                  to={website}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <img className="block w-[22px] h=[22px] object-cover" src={src} alt={text} />
                  <span className="text-color2 group-hover:text-color1 group-focus-visible:text-color1 duration-200">
                    {text}
                  </span>
                </Link>
              </div>
            ))}
          </div>
        </div>
        <div className="flex flex-col gap-[30px]">
          <h3 className="animate-hidden text-xl font-semibold">Currently learning</h3>
          <div className="grid grid-cols-2 w-full gap-[30px] sm:grid-cols-3 lg:grid-cols-4">
            {skills.currentlyLearning.map(({ src, website, text }) => (
              <Link
                key={website}
                className="animate-hidden group flex items-center gap-[8px]"
                to={website}
                target="_blank"
                rel="noopener noreferrer"
              >
                <img className="block w-[22px] h=[22px] object-cover" src={src} alt={text} />
                <span className="text-color2 group-hover:text-color1 group-focus-visible:text-color1 duration-200">
                  {text}
                </span>
              </Link>
            ))}
          </div>
        </div>
        <div className="flex flex-col gap-[30px]">
          <h3 className="animate-hidden text-xl font-semibold">Want to learn</h3>
          <div className="grid grid-cols-2 w-full gap-[30px] sm:grid-cols-3 lg:grid-cols-4">
            {skills.wantToLearn.map(({ src, website, text }) => (
              <Link
                key={website}
                className="animate-hidden group flex items-center gap-[8px]"
                to={website}
                target="_blank"
                rel="noopener noreferrer"
              >
                <img className="block w-[22px] h=[22px] object-cover" src={src} alt={text} />
                <span className="text-color2 group-hover:text-color1 group-focus-visible:text-color1 duration-200">
                  {text}
                </span>
              </Link>
            ))}
          </div>
        </div>
      </div>
    </Section>
  );
};
