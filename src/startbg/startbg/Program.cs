using System;
using System.Collections.Generic;
using System.Configuration;
using System.Diagnostics;
using System.IO;
using System.Text;

namespace startbg
{
    class Program
    {
        static void Main(string[] args)
        {
            if (args.Length < 1)
            {
                throw new ArgumentException("First argument must be the executable to launch.");
            }
            string fileName = args[0];

            ProcessStartInfo psi = new ProcessStartInfo();
            string windowStyle = ConfigurationManager.AppSettings.Get("WindowStyle");
            if (null != windowStyle) {
                psi.WindowStyle = (ProcessWindowStyle) Enum.Parse(typeof(ProcessWindowStyle), windowStyle, true);
            }
            string redirectStandardOutput = ConfigurationManager.AppSettings.Get("RedirectStandardOutput");
            if (null != redirectStandardOutput)
            {
                psi.RedirectStandardOutput = Boolean.Parse(redirectStandardOutput);
            }
            string redirectStandardError = ConfigurationManager.AppSettings.Get("RedirectStandardError");
            if (null != redirectStandardError)
            {
                psi.RedirectStandardError = Boolean.Parse(redirectStandardError);
            }
            string redirectStandardInput = ConfigurationManager.AppSettings.Get("RedirectStandardInput");
            if (null != redirectStandardInput)
            {
                psi.RedirectStandardInput = Boolean.Parse(redirectStandardInput);
            }
            psi.FileName = fileName;

            List<string> arguments = new List<string>(args);
            arguments.RemoveAt(0);
            psi.Arguments = string.Join(" ", arguments.ToArray());

            Process p = Process.Start(psi);
        }
    }
}
