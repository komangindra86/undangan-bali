const fs = require('fs');
const path = require('path');
const { withDangerousMod, withMainApplication } = require('@expo/config-plugins');

const MODULE_SOURCE = `package com.balisantih.undanganbali

import android.provider.Settings
import com.facebook.react.bridge.Promise
import com.facebook.react.bridge.ReactApplicationContext
import com.facebook.react.bridge.ReactContextBaseJavaModule
import com.facebook.react.bridge.ReactMethod

class TestLabModule(reactContext: ReactApplicationContext) : ReactContextBaseJavaModule(reactContext) {
  override fun getName(): String = "TestLab"

  @ReactMethod
  fun isRunningInTestLab(promise: Promise) {
    val setting = Settings.System.getString(
      reactApplicationContext.contentResolver,
      "firebase.test.lab"
    )
    promise.resolve(setting == "true")
  }
}
`;

const PACKAGE_SOURCE = `package com.balisantih.undanganbali

import com.facebook.react.ReactPackage
import com.facebook.react.bridge.NativeModule
import com.facebook.react.bridge.ReactApplicationContext
import com.facebook.react.uimanager.ViewManager

@Suppress("DEPRECATION")
class TestLabPackage : ReactPackage {
  override fun createNativeModules(reactContext: ReactApplicationContext): List<NativeModule> =
    listOf(TestLabModule(reactContext))

  @Suppress("OVERRIDE_DEPRECATION", "DEPRECATION")
  override fun createViewManagers(reactContext: ReactApplicationContext): List<ViewManager<*, *>> =
    emptyList()
}
`;

function withTestLabDetector(config) {
  config = withMainApplication(config, (mod) => {
    if (!mod.modResults.contents.includes('add(TestLabPackage())')) {
      mod.modResults.contents = mod.modResults.contents.replace(
        '// Packages that cannot be autolinked yet can be added manually here, for example:',
        '// Packages that cannot be autolinked yet can be added manually here, for example:\n          add(TestLabPackage())'
      );
    }

    return mod;
  });

  return withDangerousMod(config, ['android', async (mod) => {
    const sourceDir = path.join(
      mod.modRequest.platformProjectRoot,
      'app',
      'src',
      'main',
      'java',
      'com',
      'balisantih',
      'undanganbali'
    );

    fs.mkdirSync(sourceDir, { recursive: true });
    fs.writeFileSync(path.join(sourceDir, 'TestLabModule.kt'), MODULE_SOURCE);
    fs.writeFileSync(path.join(sourceDir, 'TestLabPackage.kt'), PACKAGE_SOURCE);

    return mod;
  }]);
}

module.exports = withTestLabDetector;
